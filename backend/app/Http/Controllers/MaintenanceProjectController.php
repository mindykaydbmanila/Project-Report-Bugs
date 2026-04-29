<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceProject;
use App\Models\MaintenanceProjectShare;
use Illuminate\Http\Request;

class MaintenanceProjectController extends Controller
{
    private function withCounts()
    {
        return MaintenanceProject::withCount([
            'tickets',
            'tickets as pending_count'     => fn ($q) => $q->where('status', 'Pending'),
            'tickets as in_progress_count' => fn ($q) => $q->where('status', 'In Progress'),
            'tickets as on_hold_count'     => fn ($q) => $q->where('status', 'On Hold'),
            'tickets as completed_count'   => fn ($q) => $q->where('status', 'Completed'),
            'tickets as cancelled_count'   => fn ($q) => $q->where('status', 'Cancelled'),
        ]);
    }

    private function appendMyPermission(iterable $projects, ?int $userId, ?string $userEmail): iterable
    {
        if (!$userId && !$userEmail) {
            // Unauthenticated: only see projects with link sharing enabled
            return collect($projects)->map(fn ($p) => tap($p, fn ($p) => $p->my_permission = $p->link_permission));
        }

        // Load all shares for these projects in one query
        $projectIds = collect($projects)->pluck('id');
        $shares = MaintenanceProjectShare::whereIn('maintenance_project_id', $projectIds)
            ->when($userEmail, fn ($q) => $q->where('invited_email', strtolower($userEmail)))
            ->get()
            ->keyBy('maintenance_project_id');

        return collect($projects)->map(function ($p) use ($userId, $shares) {
            if ($userId && $p->owner_id === $userId) {
                $p->my_permission = 'owner';
            } elseif ($share = $shares->get($p->id)) {
                $p->my_permission = match ($share->permission) {
                    'editor'    => 'edit',
                    'commenter' => 'comment',
                    default     => 'view',
                };
            } elseif (!$p->owner_id) {
                // Legacy project with no owner — accessible to everyone
                $p->my_permission = 'owner';
            } else {
                // Project owned by someone else, not shared with this user
                $p->my_permission = null;
            }
            return $p;
        });
    }

    public function index(Request $request)
    {
        $projects = $this->withCounts()->orderBy('name')->get();
        $user = $request->user();

        $withPerms = $this->appendMyPermission($projects, $user?->id, $user?->email);

        // Only return projects the requester has some access to
        return response()->json($withPerms->filter(fn ($p) => $p->my_permission !== null)->values());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'color'          => 'nullable|string|max:20',
            'is_active'      => 'nullable|boolean',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date',
        ]);

        $data['color']          = $data['color'] ?? '#10b981';
        $data['is_active']      = $data['is_active'] ?? true;
        $data['contract_start'] = $data['contract_start'] ?: null;
        $data['contract_end']   = $data['contract_end'] ?: null;
        $data['owner_id']       = $request->user()?->id;

        $project = MaintenanceProject::create($data);

        $fresh = $this->withCounts()->find($project->id);
        $fresh->my_permission = 'owner';

        return response()->json($fresh, 201);
    }

    public function show(MaintenanceProject $maintenanceProject, Request $request)
    {
        $fresh = $this->withCounts()->find($maintenanceProject->id);
        $user = $request->user();

        return response()->json($this->appendMyPermission(collect([$fresh]), $user?->id, $user?->email)->first());
    }

    public function update(Request $request, MaintenanceProject $maintenanceProject)
    {
        if ($maintenanceProject->owner_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'color'          => 'nullable|string|max:20',
            'is_active'      => 'nullable|boolean',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date',
        ]);

        $data['contract_start'] = $data['contract_start'] ?: null;
        $data['contract_end']   = $data['contract_end'] ?: null;

        $maintenanceProject->update($data);

        $fresh = $this->withCounts()->find($maintenanceProject->id);
        $user = $request->user();

        return response()->json($this->appendMyPermission(collect([$fresh]), $user?->id, $user?->email)->first());
    }

    public function updateLinkPermission(Request $request, MaintenanceProject $maintenanceProject)
    {
        if ($maintenanceProject->owner_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'link_permission' => 'required|in:view,comment,edit',
        ]);

        $maintenanceProject->update(['link_permission' => $data['link_permission']]);

        return response()->json(['link_permission' => $maintenanceProject->link_permission]);
    }

    public function destroy(Request $request, MaintenanceProject $maintenanceProject)
    {
        if ($maintenanceProject->owner_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $maintenanceProject->delete();
        return response()->json(null, 204);
    }
}
