<?php

namespace App\Http\Controllers;

use App\Mail\BugTicketMail;
use App\Models\AppNotification;
use App\Models\Bug;
use App\Models\DevFolder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BugController extends Controller
{
    public function index(Request $request)
    {
        $query = Bug::with('assignedDeveloper:id,name,email,avatar');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('scenario_type')) {
            $query->where('scenario_type', $request->scenario_type);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($sub) => $sub->where('title', 'like', "%$q%")
                                         ->orWhere('description', 'like', "%$q%"));
        }

        $bugs = $query->orderBy('sequence')->get();

        $summaryQuery = Bug::query();
        if ($request->filled('project_id')) {
            $summaryQuery->where('project_id', $request->project_id);
        }

        $summary = [
            'total'           => (clone $summaryQuery)->count(),
            'by_priority'     => (clone $summaryQuery)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
            'by_scenario_type'=> (clone $summaryQuery)->selectRaw('scenario_type, count(*) as count')->groupBy('scenario_type')->pluck('count', 'scenario_type'),
            'by_status'       => (clone $summaryQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
        ];

        return response()->json(['bugs' => $bugs, 'summary' => $summary]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'title'            => 'required|string|max:255',
            'subtitles'         => 'nullable|array',
            'subtitles.*.text'  => 'nullable|string|max:500',
            'subtitles.*.link'  => 'nullable|string|max:2048',
            'description'      => 'nullable|string',
            'priority'         => 'required|in:Critical,High,Medium,Low',
            'scenario_type'    => 'required|in:UI,Functionality,UI & Functionality',
            'status'              => 'required|in:Pending,Out of Scope,Ongoing,Completed',
            'date_to_accomplish'  => 'nullable|date',
            'developer_comment'   => 'nullable|string',
            'images.*'            => 'nullable|image|max:5120',
            'frontend_images.*'=> 'nullable|image|max:5120',
            'cms_images.*'     => 'nullable|image|max:5120',
        ]);

        $lastBug = Bug::where('project_id', $validated['project_id'])->orderBy('sequence', 'desc')->first();
        $validated['sequence'] = $lastBug ? $lastBug->sequence + 1 : 1;

        $validated['subtitles'] = array_values(array_filter($request->input('subtitles', []), fn($s) => !empty(trim($s['text'] ?? '')) || !empty(trim($s['link'] ?? ''))));

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['images'] = $imagePaths;

        $frontendPaths = [];
        if ($request->hasFile('frontend_images')) {
            foreach ($request->file('frontend_images') as $image) {
                $frontendPaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['frontend_images'] = $frontendPaths;

        $cmsPaths = [];
        if ($request->hasFile('cms_images')) {
            foreach ($request->file('cms_images') as $image) {
                $cmsPaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['cms_images'] = $cmsPaths;

        $bug = Bug::create($validated);

        // Notification: new ticket created
        $projectName = $bug->project?->name ?? 'Unknown Project';
        AppNotification::create([
            'type'    => 'ticket_created',
            'title'   => 'New Ticket Created',
            'message' => "#{$bug->sequence} — {$bug->title} ({$projectName})",
            'data'    => ['bug_id' => $bug->id, 'bug_sequence' => $bug->sequence, 'bug_title' => $bug->title, 'project_name' => $projectName],
        ]);

        // Log initial attachments
        $totalImages = count($imagePaths) + count($frontendPaths) + count($cmsPaths);
        if ($totalImages > 0) {
            $bug->update(['activity_log' => [[
                'type'      => 'attachment',
                'user_name' => 'Team',
                'content'   => "{$totalImages} screenshot(s) attached",
                'count'     => $totalImages,
                'timestamp' => now()->toIso8601String(),
            ]]]);
        }

        return response()->json($bug, 201);
    }

    public function show(Bug $bug)
    {
        return response()->json($bug->load('assignedDeveloper:id,name,email,avatar'));
    }

    public function update(Request $request, Bug $bug)
    {
        $validated = $request->validate([
            'title'                    => 'sometimes|required|string|max:255',
            'subtitles'                => 'nullable|array',
            'subtitles.*.text'         => 'nullable|string|max:500',
            'subtitles.*.link'         => 'nullable|string|max:2048',
            'description'              => 'nullable|string',
            'priority'                 => 'sometimes|required|in:Critical,High,Medium,Low',
            'scenario_type'            => 'sometimes|required|in:UI,Functionality,UI & Functionality',
            'status'                   => 'sometimes|required|in:Pending,Out of Scope,Ongoing,Completed',
            'developer_comment'        => 'nullable|string',
            'dev_comments_json'        => 'nullable|string',
            'date_to_accomplish'       => 'nullable|date',
            'images.*'                 => 'nullable|image|max:5120',
            'existing_images'          => 'nullable|array',
            'frontend_images.*'        => 'nullable|image|max:5120',
            'existing_frontend_images' => 'nullable|array',
            'cms_images.*'             => 'nullable|image|max:5120',
            'existing_cms_images'      => 'nullable|array',
        ]);

        if ($request->has('subtitles')) {
            $validated['subtitles'] = array_values(array_filter($request->input('subtitles', []), fn($s) => !empty(trim($s['text'] ?? '')) || !empty(trim($s['link'] ?? ''))));
        }

        if ($request->has('dev_comments_json')) {
            $decoded = json_decode($request->input('dev_comments_json'), true);
            $validated['dev_comments'] = is_array($decoded) ? $decoded : [];
            unset($validated['dev_comments_json']);
        }

        $imagePaths = $request->input('existing_images', $bug->images ?? []);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['images'] = $imagePaths;
        unset($validated['existing_images']);

        $frontendPaths = $request->input('existing_frontend_images', $bug->frontend_images ?? []);
        if ($request->hasFile('frontend_images')) {
            foreach ($request->file('frontend_images') as $image) {
                $frontendPaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['frontend_images'] = $frontendPaths;
        unset($validated['existing_frontend_images']);

        $cmsPaths = $request->input('existing_cms_images', $bug->cms_images ?? []);
        if ($request->hasFile('cms_images')) {
            foreach ($request->file('cms_images') as $image) {
                $cmsPaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['cms_images'] = $cmsPaths;
        unset($validated['existing_cms_images']);

        // Log newly added attachments
        $oldTotal = count($bug->images ?? []) + count($bug->frontend_images ?? []) + count($bug->cms_images ?? []);
        $newTotal = count($imagePaths) + count($frontendPaths) + count($cmsPaths);
        $addedCount = $newTotal - $oldTotal;
        if ($addedCount > 0) {
            $log = $bug->activity_log ?? [];
            $log[] = [
                'type'      => 'attachment',
                'user_name' => 'Team',
                'content'   => "{$addedCount} screenshot(s) added",
                'count'     => $addedCount,
                'timestamp' => now()->toIso8601String(),
            ];
            $validated['activity_log'] = $log;
        }

        $bug->update($validated);
        return response()->json($bug->load('assignedDeveloper:id,name,email,avatar'));
    }

    public function destroy(Bug $bug)
    {
        $bug->delete();
        return response()->json(['message' => 'Bug deleted successfully']);
    }

    public function summary(Request $request)
    {
        $query = Bug::query();
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return response()->json([
            'total'           => (clone $query)->count(),
            'by_priority'     => (clone $query)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
            'by_scenario_type'=> (clone $query)->selectRaw('scenario_type, count(*) as count')->groupBy('scenario_type')->pluck('count', 'scenario_type'),
            'by_status'       => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
        ]);
    }

    // ── Feature: Assign Developer ─────────────────────────────────────────────

    public function assign(Request $request, Bug $bug)
    {
        $request->validate([
            'action'          => 'required|in:add,remove',
            'developer_id'    => 'nullable|integer|exists:users,id',
            'developer_email' => 'nullable|email|max:255',
            'developer_name'  => 'nullable|string|max:255',
        ]);

        $devs   = $bug->assigned_developers ?? [];
        $action = $request->input('action');

        if ($action === 'remove') {
            $removeId    = $request->input('developer_id');
            $removeEmail = $request->input('developer_email');
            $devs = array_values(array_filter($devs, function ($d) use ($removeId, $removeEmail) {
                if ($removeId    && ($d['id']    ?? null) == $removeId)    return false;
                if ($removeEmail && ($d['email'] ?? null) === $removeEmail) return false;
                return true;
            }));
            $bug->update([
                'assigned_developers' => $devs ?: null,
                'ticket_sent_at'      => empty($devs) ? null : $bug->ticket_sent_at,
            ]);
            return response()->json($bug);
        }

        // action === 'add'
        $developerId = $request->input('developer_id');
        $email       = $request->input('developer_email');

        if ($developerId) {
            $user = User::findOrFail($developerId);
            // Avoid duplicates
            if (!collect($devs)->contains('id', $user->id)) {
                $devs[] = ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'avatar' => $user->avatar];
            }
        } elseif ($email) {
            // Check registered user first
            $user = User::where('email', $email)->first();
            if ($user) {
                if (!collect($devs)->contains('id', $user->id)) {
                    $devs[] = ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'avatar' => $user->avatar];
                }
            } else {
                // Guest email
                if (!collect($devs)->contains('email', $email)) {
                    $name   = $request->input('developer_name') ?: explode('@', $email)[0];
                    $devs[] = ['id' => null, 'name' => $name, 'email' => $email, 'avatar' => null];
                }
            }
        }

        $bug->update(['assigned_developers' => $devs ?: null]);

        // Notification: developer assigned
        $lastDev = end($devs);
        if ($lastDev) {
            $devName     = $lastDev['name'] ?? $lastDev['email'] ?? 'Developer';
            $projectName = $bug->project?->name ?? 'Unknown Project';
            AppNotification::create([
                'type'    => 'developer_assigned',
                'title'   => 'Developer Assigned',
                'message' => "{$devName} assigned to #{$bug->sequence} — {$bug->title}",
                'data'    => ['bug_id' => $bug->id, 'bug_sequence' => $bug->sequence, 'bug_title' => $bug->title, 'project_name' => $projectName, 'developer_name' => $devName],
            ]);
        }

        return response()->json($bug);
    }

    // ── Feature: Send Ticket ──────────────────────────────────────────────────

    public function sendTicket(Request $request, Bug $bug)
    {
        $devs = $bug->assigned_developers ?? [];

        if (empty($devs)) {
            return response()->json(['error' => 'No developer assigned to this bug.'], 422);
        }

        if ($bug->ticket_sent_at) {
            return response()->json(['error' => 'Ticket already sent.'], 422);
        }

        $assigner = $request->user ?? User::first();

        $bug->update(['ticket_sent_at' => now()]);

        $devNames = implode(', ', array_column($devs, 'name'));

        // Log to activity
        $log = $bug->activity_log ?? [];
        $log[] = [
            'type'      => 'ticket_sent',
            'user_name' => $assigner?->name ?? 'System',
            'content'   => "Ticket sent to {$devNames}",
            'timestamp' => now()->toIso8601String(),
        ];
        $bug->update(['activity_log' => $log]);

        // Send email to each assigned developer and ensure dev folder exists
        foreach ($devs as $devData) {
            $developer = new User([
                'name'   => $devData['name'],
                'email'  => $devData['email'],
                'avatar' => $devData['avatar'] ?? null,
            ]);
            try {
                Mail::to($developer->email)->send(new BugTicketMail($bug, $developer, $assigner ?? $developer));
            } catch (\Exception $e) {
                \Log::error('BugTicketMail failed: ' . $e->getMessage());
            }

            DevFolder::firstOrCreate(
                ['developer_email' => strtolower($devData['email'])],
                [
                    'token'          => Str::random(48),
                    'developer_name' => $devData['name'],
                    'visibility'     => 'public',
                ]
            );
        }

        return response()->json($bug->load('assignedDeveloper:id,name,email,avatar'));
    }

    public function resendTicket(Request $request, Bug $bug)
    {
        $devs = $bug->assigned_developers ?? [];

        if (empty($devs)) {
            return response()->json(['error' => 'No developer assigned to this bug.'], 422);
        }

        $assigner = $request->user ?? User::first();

        $bug->update(['ticket_sent_at' => now()]);

        $devNames = implode(', ', array_column($devs, 'name'));

        $log = $bug->activity_log ?? [];
        $log[] = [
            'type'      => 'ticket_resent',
            'user_name' => $assigner?->name ?? 'System',
            'content'   => "Ticket resent to {$devNames}",
            'timestamp' => now()->toIso8601String(),
        ];
        $bug->update(['activity_log' => $log]);

        foreach ($devs as $devData) {
            $developer = new User([
                'name'   => $devData['name'],
                'email'  => $devData['email'],
                'avatar' => $devData['avatar'] ?? null,
            ]);
            try {
                Mail::to($developer->email)->send(new BugTicketMail($bug, $developer, $assigner ?? $developer));
            } catch (\Exception $e) {
                \Log::error('BugTicketMail resend failed: ' . $e->getMessage());
            }

            DevFolder::firstOrCreate(
                ['developer_email' => strtolower($devData['email'])],
                [
                    'token'          => Str::random(48),
                    'developer_name' => $devData['name'],
                    'visibility'     => 'public',
                ]
            );
        }

        return response()->json($bug->fresh());
    }

    // ── Feature: Ticket Detail ────────────────────────────────────────────────

    public function ticket(Bug $bug)
    {
        return response()->json($bug->load(['assignedDeveloper:id,name,email,avatar', 'project:id,name,color']));
    }

    public function addComment(Request $request, Bug $bug)
    {
        $request->validate([
            'message'   => 'required|string|max:2000',
            'author'    => 'required|string|max:255',
            'author_email' => 'nullable|email',
        ]);

        $log = $bug->activity_log ?? [];
        $log[] = [
            'type'         => 'comment',
            'user_name'    => $request->input('author'),
            'author_email' => $request->input('author_email'),
            'content'      => $request->input('message'),
            'timestamp'    => now()->toIso8601String(),
        ];
        $bug->update(['activity_log' => $log]);

        return response()->json($bug->load(['assignedDeveloper:id,name,email,avatar', 'project:id,name,color']));
    }

    public function updateStatus(Request $request, Bug $bug)
    {
        $request->validate([
            'status'    => 'required|in:Pending,Out of Scope,Ongoing,Completed',
            'author'    => 'nullable|string|max:255',
        ]);

        $oldStatus = $bug->status;
        $newStatus = $request->input('status');

        $bug->update(['status' => $newStatus]);

        $log = $bug->activity_log ?? [];
        $log[] = [
            'type'      => 'status_change',
            'user_name' => $request->input('author', 'Developer'),
            'content'   => "Status changed from {$oldStatus} to {$newStatus}",
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'timestamp' => now()->toIso8601String(),
        ];
        $bug->update(['activity_log' => $log]);

        // Notification: ticket completed
        if ($newStatus === 'Completed' && $oldStatus !== 'Completed') {
            $projectName = $bug->project?->name ?? 'Unknown Project';
            AppNotification::create([
                'type'    => 'ticket_completed',
                'title'   => 'Ticket Completed',
                'message' => "#{$bug->sequence} — {$bug->title} marked as Completed",
                'data'    => ['bug_id' => $bug->id, 'bug_sequence' => $bug->sequence, 'bug_title' => $bug->title, 'project_name' => $projectName],
            ]);
        }

        return response()->json($bug->load(['assignedDeveloper:id,name,email,avatar', 'project:id,name,color']));
    }

    public function resolve(Request $request, Bug $bug)
    {
        $request->validate([
            'author' => 'nullable|string|max:255',
        ]);

        $authorName = $request->input('author', 'Developer');

        $bug->update(['status' => 'Completed', 'resolved_by' => $authorName, 'dev_status' => 'Ready for QA']);

        $log = $bug->activity_log ?? [];
        $log[] = [
            'type'      => 'resolved',
            'user_name' => $authorName,
            'content'   => "{$authorName} marked this ticket as resolved.",
            'timestamp' => now()->toIso8601String(),
        ];
        $bug->update(['activity_log' => $log]);

        // Notification: resolved
        $projectName = $bug->project?->name ?? 'Unknown Project';
        AppNotification::create([
            'type'    => 'ticket_completed',
            'title'   => 'Ticket Resolved',
            'message' => "{$authorName} resolved #{$bug->sequence} — {$bug->title}",
            'data'    => ['bug_id' => $bug->id, 'bug_sequence' => $bug->sequence, 'bug_title' => $bug->title, 'project_name' => $projectName],
        ]);

        return response()->json($bug->load(['assignedDeveloper:id,name,email,avatar', 'project:id,name,color']));
    }

    // ── Dev Status ────────────────────────────────────────────────────────────

    public function updateDevStatus(Request $request, Bug $bug)
    {
        $request->validate([
            'dev_status' => 'required|in:Not Started,In Progress,Ready for QA,Blocked',
            'author'     => 'nullable|string|max:255',
        ]);

        $oldStatus = $bug->dev_status ?? 'Not Started';
        $newStatus = $request->input('dev_status');

        $bug->update(['dev_status' => $newStatus]);

        $log = $bug->activity_log ?? [];
        $log[] = [
            'type'      => 'dev_status_change',
            'user_name' => $request->input('author', 'Developer'),
            'content'   => "Dev status changed from {$oldStatus} to {$newStatus}",
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'timestamp' => now()->toIso8601String(),
        ];
        $bug->update(['activity_log' => $log]);

        // Notification: Ready for QA or Blocked
        $projectName = $bug->project?->name ?? 'Unknown Project';
        if ($newStatus === 'Ready for QA') {
            AppNotification::create([
                'type'    => 'ready_for_qa',
                'title'   => 'Ready for QA',
                'message' => "#{$bug->sequence} — {$bug->title} is ready for QA testing",
                'data'    => ['bug_id' => $bug->id, 'bug_sequence' => $bug->sequence, 'bug_title' => $bug->title, 'project_name' => $projectName],
            ]);
        } elseif ($newStatus === 'Blocked') {
            AppNotification::create([
                'type'    => 'blocked',
                'title'   => 'Ticket Blocked',
                'message' => "#{$bug->sequence} — {$bug->title} is blocked",
                'data'    => ['bug_id' => $bug->id, 'bug_sequence' => $bug->sequence, 'bug_title' => $bug->title, 'project_name' => $projectName],
            ]);
        }

        return response()->json($bug->load(['assignedDeveloper:id,name,email,avatar', 'project:id,name,color']));
    }

    // ── Date to Accomplish ────────────────────────────────────────────────────

    public function updateDateToAccomplish(Request $request, Bug $bug)
    {
        $request->validate([
            'date_to_accomplish' => 'nullable|date',
            'author'             => 'nullable|string|max:255',
        ]);

        $old = $bug->date_to_accomplish?->format('Y-m-d');
        $new = $request->input('date_to_accomplish');

        $bug->update(['date_to_accomplish' => $new ?: null]);

        $log = $bug->activity_log ?? [];
        $log[] = [
            'type'      => 'date_to_accomplish_change',
            'user_name' => $request->input('author', 'Developer'),
            'content'   => $new
                ? "Date to accomplish set to {$new}" . ($old ? " (was {$old})" : '')
                : 'Date to accomplish cleared',
            'old_value' => $old,
            'new_value' => $new,
            'timestamp' => now()->toIso8601String(),
        ];
        $bug->update(['activity_log' => $log]);

        return response()->json($bug->load(['assignedDeveloper:id,name,email,avatar', 'project:id,name,color']));
    }

    // ── Team Members ──────────────────────────────────────────────────────────

    public function teamMembers()
    {
        $users = User::select('id', 'name', 'email', 'avatar')->orderBy('name')->get();
        return response()->json($users);
    }

    // ── Clear all activity logs ───────────────────────────────────────────────

    public function clearActivityLog()
    {
        Bug::query()->update(['activity_log' => null]);
        return response()->json(['message' => 'Activity log cleared.']);
    }
}
