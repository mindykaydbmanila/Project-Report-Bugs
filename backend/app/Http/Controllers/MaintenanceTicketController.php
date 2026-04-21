<?php

namespace App\Http\Controllers;

use App\Mail\MaintenanceNotificationMail;
use App\Models\AppNotification;
use App\Models\MaintenanceProject;
use App\Models\MaintenanceTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaintenanceTicketController extends Controller
{
    public function index(MaintenanceProject $maintenanceProject)
    {
        $tickets = $maintenanceProject->tickets()->orderBy('sequence')->get();
        return response()->json($tickets);
    }

    public function store(Request $request, MaintenanceProject $maintenanceProject)
    {
        $data = $request->validate([
            'client'            => 'required|string|max:255',
            'request'           => 'required|string',
            'sent_thru'         => 'nullable|string|max:50',
            'date_received'     => 'nullable|date',
            'target_date'       => 'nullable|date',
            'completion_date'   => 'nullable|date',
            'assigned_devs'     => 'nullable|array',
            'assigned_devs.*'   => 'email',
            'assigned_qa'       => 'nullable|array',
            'assigned_qa.*'     => 'email',
            'status'            => 'nullable|string|in:Pending,In Progress,On Hold,Completed,Cancelled',
            'dev_status'        => 'nullable|string|in:Not Started,In Progress,Ready for QA,Blocked',
            'notes'             => 'nullable|string',
            'attachments'       => 'nullable|array',
            'attachments.*'     => 'file|max:20480|mimes:jpeg,jpg,png,gif,webp,pdf',
        ]);

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = Storage::url($file->store('maintenance-attachments', 'public'));
            }
        }
        $data['attachments'] = $attachmentPaths;

        $maxSeq = $maintenanceProject->tickets()->max('sequence') ?? 0;
        $data['sequence']               = $maxSeq + 1;
        $data['maintenance_project_id'] = $maintenanceProject->id;
        $data['sent_thru']              = $data['sent_thru'] ?? 'Email';
        $data['status']                 = $data['status'] ?? 'Pending';

        $ticket = MaintenanceTicket::create($data);

        AppNotification::create([
            'type'    => 'mt_ticket_created',
            'title'   => 'New maintenance ticket',
            'message' => "{$ticket->ticket_number} · {$ticket->client} — {$maintenanceProject->name}",
            'data'    => [
                'ticket_id'     => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'project_id'    => $maintenanceProject->id,
                'project_name'  => $maintenanceProject->name,
                'client'        => $ticket->client,
            ],
        ]);

        $recipients = array_merge(
            $data['assigned_devs'] ?? [],
            $data['assigned_qa']   ?? []
        );

        if (!empty($recipients)) {
            $this->sendNotifications($ticket, $maintenanceProject, $recipients);
            $ticket->update(['notification_sent_at' => now()]);
            $ticket->refresh();
        }

        return response()->json($ticket, 201);
    }

    public function show(MaintenanceProject $maintenanceProject, MaintenanceTicket $maintenanceTicket)
    {
        abort_unless($maintenanceTicket->maintenance_project_id === $maintenanceProject->id, 404);
        return response()->json($maintenanceTicket);
    }

    public function update(Request $request, MaintenanceProject $maintenanceProject, MaintenanceTicket $maintenanceTicket)
    {
        abort_unless($maintenanceTicket->maintenance_project_id === $maintenanceProject->id, 404);

        $data = $request->validate([
            'client'                    => 'sometimes|required|string|max:255',
            'request'                   => 'sometimes|required|string',
            'sent_thru'                 => 'nullable|string|max:50',
            'date_received'             => 'nullable|date',
            'target_date'               => 'nullable|date',
            'completion_date'           => 'nullable|date',
            'assigned_devs'             => 'nullable|array',
            'assigned_devs.*'           => 'email',
            'assigned_qa'               => 'nullable|array',
            'assigned_qa.*'             => 'email',
            'status'                    => 'nullable|string|in:Pending,In Progress,On Hold,Completed,Cancelled',
            'dev_status'                => 'nullable|string|in:Not Started,In Progress,Ready for QA,Blocked',
            'notes'                     => 'nullable|string',
            'comments_json'             => 'nullable|string',
            'existing_attachments'      => 'nullable|array',
            'existing_attachments.*'    => 'string',
            'new_attachments'           => 'nullable|array',
            'new_attachments.*'         => 'file|max:20480|mimes:jpeg,jpg,png,gif,webp,pdf',
        ]);

        // Handle comments JSON
        if (isset($data['comments_json'])) {
            $data['comments'] = json_decode($data['comments_json'], true) ?? [];
            unset($data['comments_json']);
        }

        // Build final attachments list: kept existing + newly uploaded
        $kept = $data['existing_attachments'] ?? ($maintenanceTicket->attachments ?? []);
        unset($data['existing_attachments']);

        // Delete removed attachment files from storage
        foreach (array_diff($maintenanceTicket->attachments ?? [], $kept) as $url) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }

        $newPaths = [];
        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $file) {
                $newPaths[] = Storage::url($file->store('maintenance-attachments', 'public'));
            }
        }

        $data['attachments'] = array_merge($kept, $newPaths);
        unset($data['new_attachments']);

        $oldStatus = $maintenanceTicket->status;
        $maintenanceTicket->update($data);

        // Fire in-app notification when status changes
        $newStatus = $maintenanceTicket->fresh()->status;
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            if ($data['status'] === 'Completed') {
                AppNotification::create([
                    'type'    => 'mt_ticket_completed',
                    'title'   => 'Ticket completed',
                    'message' => "{$maintenanceTicket->ticket_number} · {$maintenanceTicket->client} — {$maintenanceProject->name}",
                    'data'    => [
                        'ticket_id'     => $maintenanceTicket->id,
                        'ticket_number' => $maintenanceTicket->ticket_number,
                        'project_id'    => $maintenanceProject->id,
                        'project_name'  => $maintenanceProject->name,
                        'client'        => $maintenanceTicket->client,
                    ],
                ]);
            } else {
                AppNotification::create([
                    'type'    => 'mt_status_changed',
                    'title'   => "Status → {$data['status']}",
                    'message' => "{$maintenanceTicket->ticket_number} · {$maintenanceTicket->client} — {$maintenanceProject->name}",
                    'data'    => [
                        'ticket_id'     => $maintenanceTicket->id,
                        'ticket_number' => $maintenanceTicket->ticket_number,
                        'project_id'    => $maintenanceProject->id,
                        'project_name'  => $maintenanceProject->name,
                        'client'        => $maintenanceTicket->client,
                        'new_status'    => $data['status'],
                    ],
                ]);
            }
        }

        return response()->json($maintenanceTicket->fresh());
    }

    public function destroy(MaintenanceProject $maintenanceProject, MaintenanceTicket $maintenanceTicket)
    {
        abort_unless($maintenanceTicket->maintenance_project_id === $maintenanceProject->id, 404);

        foreach ($maintenanceTicket->attachments ?? [] as $url) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }

        $maintenanceTicket->delete();
        return response()->json(null, 204);
    }

    public function notify(MaintenanceProject $maintenanceProject, MaintenanceTicket $maintenanceTicket)
    {
        abort_unless($maintenanceTicket->maintenance_project_id === $maintenanceProject->id, 404);

        $recipients = array_unique(array_merge(
            $maintenanceTicket->assigned_devs ?? [],
            $maintenanceTicket->assigned_qa   ?? []
        ));

        if (empty($recipients)) {
            return response()->json(['error' => 'No recipients assigned.'], 422);
        }

        $this->sendNotifications($maintenanceTicket, $maintenanceProject, $recipients);
        $maintenanceTicket->update(['notification_sent_at' => now()]);

        return response()->json([
            'sent_at'    => $maintenanceTicket->fresh()->notification_sent_at,
            'recipients' => $recipients,
        ]);
    }

    // ── All unique devs across maintenance tickets ────────────────────────────

    public function allDevs()
    {
        $tickets = MaintenanceTicket::with('project')
            ->whereHas('project', fn($q) => $q->where('is_active', true))
            ->where(fn($q) => $q->whereNotNull('assigned_devs')->orWhereNotNull('assigned_qa'))
            ->get(['id', 'assigned_devs', 'assigned_qa', 'maintenance_project_id', 'status']);

        $map = []; // email => { email, roles, ticket_count, pending, in_progress, completed, projects }

        foreach ($tickets as $ticket) {
            $projectName = optional($ticket->project)->name ?? 'Unknown';
            $status      = $ticket->status ?? 'Pending';

            $allEmails = array_unique(array_merge(
                $ticket->assigned_devs ?? [],
                $ticket->assigned_qa   ?? []
            ));

            foreach ($allEmails as $email) {
                if (!isset($map[$email])) {
                    $map[$email] = [
                        'email'        => $email,
                        'roles'        => [],
                        'ticket_count' => 0,
                        'pending'      => 0,
                        'in_progress'  => 0,
                        'completed'    => 0,
                        'projects'     => [],
                    ];
                }
                $map[$email]['ticket_count']++;
                if ($status === 'Pending')     $map[$email]['pending']++;
                if ($status === 'In Progress') $map[$email]['in_progress']++;
                if ($status === 'Completed')   $map[$email]['completed']++;
                if (!in_array($projectName, $map[$email]['projects'])) {
                    $map[$email]['projects'][] = $projectName;
                }
            }

            foreach ($ticket->assigned_devs ?? [] as $email) {
                if (!in_array('dev', $map[$email]['roles'])) $map[$email]['roles'][] = 'dev';
            }
            foreach ($ticket->assigned_qa ?? [] as $email) {
                if (!in_array('qa', $map[$email]['roles'])) $map[$email]['roles'][] = 'qa';
            }
        }

        return response()->json(array_values($map));
    }

    // ── Dev folder (all maintenance tickets assigned to an email) ─────────────

    public function devFolder(Request $request)
    {
        $email = $request->query('email');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'A valid email is required.'], 422);
        }

        $tickets = MaintenanceTicket::with('project')
            ->whereHas('project', fn($q) => $q->where('is_active', true))
            ->where(function ($q) use ($email) {
                $q->whereJsonContains('assigned_devs', $email)
                  ->orWhereJsonContains('assigned_qa', $email);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'email'   => $email,
            'tickets' => $tickets,
        ]);
    }

    // ── Public ticket view (accessible via email link) ────────────────────────

    public function publicShow(MaintenanceTicket $maintenanceTicket)
    {
        $maintenanceTicket->load('project');
        return response()->json($maintenanceTicket);
    }

    public function addComment(Request $request, MaintenanceTicket $maintenanceTicket)
    {
        $data = $request->validate([
            'message' => 'required|string',
            'author'  => 'nullable|string|max:100',
        ]);

        $comments   = $maintenanceTicket->comments ?? [];
        $comments[] = [
            'message'   => $data['message'],
            'author'    => $data['author'] ?? 'Anonymous',
            'timestamp' => now()->toIso8601String(),
        ];

        $maintenanceTicket->update(['comments' => $comments]);
        return response()->json($maintenanceTicket->fresh()->load('project'));
    }

    public function updateTicketDevStatus(Request $request, MaintenanceTicket $maintenanceTicket)
    {
        $data = $request->validate([
            'dev_status' => 'required|string|in:Not Started,In Progress,Ready for QA,Blocked',
        ]);

        $maintenanceTicket->update($data);
        return response()->json($maintenanceTicket->fresh()->load('project'));
    }

    public function updateTicketStatus(Request $request, MaintenanceTicket $maintenanceTicket)
    {
        $data = $request->validate([
            'status' => 'required|string|in:Pending,In Progress,On Hold,Completed,Cancelled',
        ]);

        $oldStatus = $maintenanceTicket->status;
        $maintenanceTicket->update($data);

        if ($data['status'] !== $oldStatus) {
            $project = $maintenanceTicket->project;
            if ($data['status'] === 'Completed') {
                AppNotification::create([
                    'type'    => 'mt_ticket_completed',
                    'title'   => 'Ticket completed',
                    'message' => "{$maintenanceTicket->ticket_number} · {$maintenanceTicket->client}" . ($project ? " — {$project->name}" : ''),
                    'data'    => [
                        'ticket_id'     => $maintenanceTicket->id,
                        'ticket_number' => $maintenanceTicket->ticket_number,
                        'project_id'    => $maintenanceTicket->maintenance_project_id,
                        'project_name'  => $project?->name,
                        'client'        => $maintenanceTicket->client,
                    ],
                ]);
            } else {
                AppNotification::create([
                    'type'    => 'mt_status_changed',
                    'title'   => "Status → {$data['status']}",
                    'message' => "{$maintenanceTicket->ticket_number} · {$maintenanceTicket->client}" . ($project ? " — {$project->name}" : ''),
                    'data'    => [
                        'ticket_id'     => $maintenanceTicket->id,
                        'ticket_number' => $maintenanceTicket->ticket_number,
                        'project_id'    => $maintenanceTicket->maintenance_project_id,
                        'project_name'  => $project?->name,
                        'client'        => $maintenanceTicket->client,
                        'new_status'    => $data['status'],
                    ],
                ]);
            }
        }

        return response()->json($maintenanceTicket->fresh()->load('project'));
    }

    private function sendNotifications(MaintenanceTicket $ticket, MaintenanceProject $project, array $emails): void
    {
        foreach (array_values($emails) as $i => $email) {
            if ($i > 0) usleep(1100000); // 1.1s between emails — stays under Mailtrap free-plan rate limit
            try {
                Mail::to($email)->send(new MaintenanceNotificationMail($ticket, $project, $email));
            } catch (\Exception $e) {
                \Log::warning("Failed to send maintenance notification to {$email}: " . $e->getMessage());
            }
        }
    }
}
