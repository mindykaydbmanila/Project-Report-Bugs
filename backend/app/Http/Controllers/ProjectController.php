<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    private function withCounts()
    {
        return Project::withCount([
            'bugs',
            'bugs as pending_count'   => fn($q) => $q->where('status', 'Pending'),
            'bugs as ongoing_count'   => fn($q) => $q->where('status', 'Ongoing'),
            'bugs as completed_count' => fn($q) => $q->where('status', 'Completed'),
            'bugs as critical_count'  => fn($q) => $q->where('priority', 'Critical'),
        ]);
    }

    private function appendMyPermission(iterable $projects, ?int $userId, ?string $userEmail): iterable
    {
        if (!$userId && !$userEmail) {
            return collect($projects)->map(fn ($p) => tap($p, fn ($p) => $p->my_permission = $p->link_permission ?? 'view'));
        }

        $projectIds = collect($projects)->pluck('id');
        $shares = ProjectShare::whereIn('project_id', $projectIds)
            ->when($userEmail, fn ($q) => $q->where('invited_email', strtolower($userEmail)))
            ->get()
            ->keyBy('project_id');

        return collect($projects)->map(function ($p) use ($userId, $shares) {
            if ($userId && (int)$p->owner_id === (int)$userId) {
                $p->my_permission = 'owner';
            } elseif ($share = $shares->get($p->id)) {
                $p->my_permission = $share->permission === 'editor' ? 'edit' : 'view';
            } else {
                // Legacy projects without owner_id get full access; owned but unshared = no access
                $p->my_permission = is_null($p->owner_id) ? 'owner' : 'none';
            }
            return $p;
        });
    }

    public function index(Request $request)
    {
        $projects = $this->withCounts()->orderBy('created_at', 'asc')->get();
        $user = $request->user();

        return response()->json(
            $this->appendMyPermission($projects, $user?->id, $user?->email)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:20',
            'is_active'   => 'sometimes|boolean',
        ]);

        if ($request->user()) {
            $validated['owner_id'] = $request->user()->id;
        }

        $project = Project::create($validated);
        $fresh = $this->withCounts()->find($project->id);
        $fresh->my_permission = 'owner';

        return response()->json($fresh, 201);
    }

    public function show(Project $project, Request $request)
    {
        $fresh = $this->withCounts()->find($project->id);
        $user = $request->user();

        return response()->json(
            $this->appendMyPermission(collect([$fresh]), $user?->id, $user?->email)->first()
        );
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:20',
            'is_active'   => 'sometimes|boolean',
        ]);

        $project->update($validated);
        $fresh = $this->withCounts()->find($project->id);
        $user = $request->user();

        return response()->json(
            $this->appendMyPermission(collect([$fresh]), $user?->id, $user?->email)->first()
        );
    }

    public function updateLinkPermission(Request $request, Project $project)
    {
        $data = $request->validate([
            'link_permission' => 'required|in:view,edit',
        ]);

        $project->update(['link_permission' => $data['link_permission']]);

        return response()->json(['link_permission' => $project->link_permission]);
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json(['message' => 'Project deleted']);
    }
}
