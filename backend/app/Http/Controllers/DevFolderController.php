<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\DevFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DevFolderController extends Controller
{
    /**
     * List all dev folders.
     * GET /api/dev-folders
     */
    public function index()
    {
        $folders = DevFolder::orderBy('developer_name')->get();
        return response()->json($folders);
    }

    /**
     * Generate or retrieve the share token for a developer.
     * POST /api/dev-folders
     */
    /** Gmail-style palette — deterministic from email, changeable later */
    private static array $colorPalette = [
        '#4f46e5','#7c3aed','#db2777','#dc2626','#ea580c',
        '#d97706','#16a34a','#0891b2','#2563eb','#9333ea',
        '#0d9488','#65a30d','#be185d','#0369a1','#92400e','#374151',
    ];

    private static function pickColor(string $email): string
    {
        $idx = abs(crc32(strtolower($email))) % count(self::$colorPalette);
        return self::$colorPalette[$idx];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'developer_email' => 'required|email',
            'developer_name'  => 'required|string|max:255',
            'visibility'      => 'nullable|in:private,public',
        ]);

        $folder = DevFolder::firstOrCreate(
            ['developer_email' => strtolower($validated['developer_email'])],
            [
                'token'          => Str::random(48),
                'developer_name' => $validated['developer_name'],
                'visibility'     => $validated['visibility'] ?? 'public',
                'avatar_color'   => self::pickColor($validated['developer_email']),
            ]
        );

        // Update visibility if explicitly passed and folder already existed
        if ($folder->wasRecentlyCreated === false && isset($validated['visibility'])) {
            $folder->update(['visibility' => $validated['visibility']]);
        }

        return response()->json([
            'token'      => $folder->token,
            'visibility' => $folder->visibility,
            'url'        => rtrim(config('app.frontend_url', 'http://localhost:3000'), '/') . '/dev-folder/' . $folder->token,
        ]);
    }

    /**
     * Update visibility of an existing folder.
     * PATCH /api/dev-folders/{token}
     */
    public function update(Request $request, string $token)
    {
        $folder = DevFolder::where('token', $token)->firstOrFail();

        $validated = $request->validate([
            'visibility'   => 'nullable|in:private,public',
            'avatar_color' => 'nullable|string|max:20|regex:/^#[0-9a-fA-F]{3,8}$/',
        ]);

        $folder->update(array_filter($validated, fn($v) => $v !== null));

        return response()->json(['success' => true, 'visibility' => $folder->visibility]);
    }

    /**
     * Delete a folder token.
     * DELETE /api/dev-folders/{token}
     */
    public function destroy(string $token)
    {
        DevFolder::where('token', $token)->firstOrFail()->delete();

        return response()->json(['message' => 'Folder link removed']);
    }

    /**
     * Aggregate summary for the Dev Folders dashboard.
     * GET /api/dev-folders/summary
     */
    public function summary()
    {
        $bugs    = Bug::whereHas('project', fn($q) => $q->where('is_active', true))->get();
        $folders = DevFolder::orderBy('developer_name')->get();

        $overall = [
            'total_bugs'      => $bugs->count(),
            'total_active'    => $bugs->whereIn('dev_status', ['In Progress', 'Ready for QA'])->count(),
            'pending'         => $bugs->where('status', 'Pending')->count(),
            'ongoing_fixing'  => $bugs->where('dev_status', 'In Progress')->count(),
            'ongoing_qa'      => $bugs->where('status', 'Ongoing')->count(),
            'sent_back'       => $bugs->where('dev_status', 'Blocked')->count(),
            'total_completed' => $bugs->where('status', 'Completed')->count(),
            'critical'        => $bugs->where('priority', 'Critical')->count(),
            'high'            => $bugs->where('priority', 'High')->count(),
            'medium'          => $bugs->where('priority', 'Medium')->count(),
            'low'             => $bugs->where('priority', 'Low')->count(),
        ];

        $developers = $folders->map(function ($folder) use ($bugs) {
            $devBugs = $bugs->filter(function ($bug) use ($folder) {
                foreach ($bug->assigned_developers ?? [] as $dev) {
                    if (strtolower($dev['email'] ?? '') === strtolower($folder->developer_email)) {
                        return true;
                    }
                }
                return false;
            });

            return [
                'name'         => $folder->developer_name,
                'email'        => $folder->developer_email,
                'avatar_color' => $folder->avatar_color ?? self::pickColor($folder->developer_email),
                'pending'      => $devBugs->where('status', 'Pending')->count(),
                'active'       => $devBugs->whereIn('dev_status', ['In Progress', 'Ready for QA'])->count(),
                'completed'    => $devBugs->where('status', 'Completed')->count(),
            ];
        })->values();

        return response()->json([
            'date'       => now()->format('F j, Y'),
            'overall'    => $overall,
            'developers' => $developers,
        ]);
    }

    /**
     * Public endpoint — returns all tickets for the dev identified by the token.
     * GET /api/dev-folders/{token}/bugs
     */
    public function bugs(Request $request, string $token)
    {
        $folder = DevFolder::where('token', $token)->firstOrFail();

        // Private folders require email verification
        if ($folder->visibility === 'private') {
            $email = $request->query('email');
            if (!$email || strtolower($email) !== strtolower($folder->developer_email)) {
                return response()->json(['requires_auth' => true, 'developer_name' => $folder->developer_name], 403);
            }
        }

        $bugs = Bug::query()
            ->with('project')
            ->whereHas('project', fn($q) => $q->where('is_active', true))
            ->get()
            ->filter(function ($bug) use ($folder) {
                $devs = $bug->assigned_developers ?? [];
                foreach ($devs as $dev) {
                    if (strtolower($dev['email'] ?? '') === strtolower($folder->developer_email)) {
                        return true;
                    }
                }
                return false;
            })
            ->values();

        // Sort: Ongoing first, then Pending, then Completed
        $order = ['Ongoing' => 0, 'Pending' => 1, 'Out of Scope' => 2, 'Completed' => 3];
        $bugs = $bugs->sortBy(fn($b) => $order[$b->status] ?? 99)->values();

        return response()->json([
            'folder' => [
                'developer_name'  => $folder->developer_name,
                'developer_email' => $folder->developer_email,
                'visibility'      => $folder->visibility,
            ],
            'bugs' => $bugs,
        ]);
    }
}
