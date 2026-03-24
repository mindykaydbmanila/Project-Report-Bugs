<?php

namespace App\Http\Controllers;

use App\Mail\ProjectShareInvite;
use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProjectShareController extends Controller
{
    private function formatShare(ProjectShare $share): array
    {
        return [
            'id'         => $share->id,
            'email'      => $share->invited_email,
            'permission' => $share->permission,
            'accepted'   => $share->accepted_at !== null,
            'user'       => $share->user ? [
                'name'   => $share->user->name,
                'avatar' => $share->user->avatar,
            ] : null,
        ];
    }

    private function assertOwner(Project $project, $user): bool
    {
        // Legacy unowned project — first logged-in user to manage it claims ownership
        if (! $project->owner_id) {
            $project->update(['owner_id' => $user->id]);
            return true;
        }
        return $project->owner_id === $user->id;
    }

    public function index(Project $project, Request $request)
    {
        $user = $request->user();

        if (! $this->assertOwner($project, $user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json(
            $project->shares()->with('user')->get()->map(fn ($s) => $this->formatShare($s))
        );
    }

    public function store(Project $project, Request $request)
    {
        $user = $request->user();

        if (! $this->assertOwner($project, $user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'email'      => 'required|email',
            'permission' => 'required|in:viewer,editor',
        ]);

        if (strtolower($validated['email']) === strtolower($user->email)) {
            return response()->json(['error' => 'You cannot share a project with yourself'], 422);
        }

        $invitedUser = User::where('email', $validated['email'])->first();

        $share = ProjectShare::updateOrCreate(
            ['project_id' => $project->id, 'invited_email' => $validated['email']],
            [
                'user_id'      => $invitedUser?->id,
                'invited_by'   => $user->id,
                'permission'   => $validated['permission'],
                'accepted_at'  => $invitedUser ? now() : null,
            ]
        );

        $share->load('user');

        // Send invitation email (only if user doesn't already have access)
        if (! $invitedUser) {
            Mail::to($validated['email'])->send(new ProjectShareInvite($project, $share, $user));
        }

        return response()->json($this->formatShare($share), 201);
    }

    public function update(Project $project, ProjectShare $share, Request $request)
    {
        $user = $request->user();

        if (! $this->assertOwner($project, $user) || $share->project_id !== $project->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'permission' => 'required|in:viewer,editor',
        ]);

        $share->update($validated);

        return response()->json(['success' => true]);
    }

    public function destroy(Project $project, ProjectShare $share, Request $request)
    {
        $user = $request->user();

        if (! $this->assertOwner($project, $user) || $share->project_id !== $project->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $share->delete();

        return response()->json(['message' => 'Share removed']);
    }
}
