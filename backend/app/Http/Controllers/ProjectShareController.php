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

        $email = strtolower($validated['email']);

        $invitedUser = User::whereRaw('LOWER(email) = ?', [$email])->first();

        $share = ProjectShare::updateOrCreate(
            ['project_id' => $project->id, 'invited_email' => $email],
            [
                'user_id'      => $invitedUser?->id,
                'invited_by'   => $user->id,
                'permission'   => $validated['permission'],
                'accepted_at'  => $invitedUser ? now() : null,
            ]
        );

        $share->load('user');

        Mail::to($email)->send(new ProjectShareInvite($project, $share, $user));

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

    public function mySharedProjects(Request $request)
    {
        $user  = $request->user();
        $email = strtolower($user->email);

        $shares = ProjectShare::with(['project.bugs', 'inviter'])
            ->where(function ($q) use ($user, $email) {
                $q->where('user_id', $user->id)
                  ->orWhereRaw('LOWER(invited_email) = ?', [$email]);
            })
            ->whereNotNull('accepted_at')
            ->get();

        return response()->json(
            $shares->map(function ($share) {
                $project = $share->project;
                if (! $project) return null;

                $bugs = $project->bugs;

                return [
                    'id'                => $project->id,
                    'name'              => $project->name,
                    'description'       => $project->description,
                    'color'             => $project->color,
                    'is_active'         => $project->is_active,
                    'my_permission'     => $share->permission,
                    'accepted_at'       => $share->accepted_at,
                    'invited_by_name'   => $share->inviter?->name,
                    'invited_by_avatar' => $share->inviter?->avatar,
                    'bugs_count'        => $bugs->count(),
                    'pending_count'     => $bugs->where('status', 'Pending')->count(),
                    'ongoing_count'     => $bugs->where('status', 'Ongoing')->count(),
                    'completed_count'   => $bugs->where('status', 'Completed')->count(),
                    'critical_count'    => $bugs->where('priority', 'Critical')->count(),
                ];
            })->filter()->values()
        );
    }

    public function leave(Project $project, Request $request)
    {
        $user  = $request->user();
        $email = strtolower($user->email);

        $share = ProjectShare::where('project_id', $project->id)
            ->where(function ($q) use ($user, $email) {
                $q->where('user_id', $user->id)
                  ->orWhereRaw('LOWER(invited_email) = ?', [$email]);
            })
            ->first();

        if (! $share) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $share->delete();

        return response()->json(['message' => 'Left project']);
    }
}
