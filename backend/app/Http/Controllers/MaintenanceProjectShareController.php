<?php

namespace App\Http\Controllers;

use App\Mail\MaintenanceShareInvite;
use App\Models\MaintenanceProject;
use App\Models\MaintenanceProjectShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MaintenanceProjectShareController extends Controller
{
    // Permission value used in DB → label shown in frontend
    private const PERM_MAP = [
        'view'    => 'viewer',
        'comment' => 'commenter',
        'edit'    => 'editor',
    ];

    private const PERM_REVERSE = [
        'viewer'    => 'view',
        'commenter' => 'comment',
        'editor'    => 'edit',
    ];

    private function formatShare(MaintenanceProjectShare $share): array
    {
        return [
            'id'         => $share->id,
            'email'      => $share->invited_email,
            'permission' => self::PERM_REVERSE[$share->permission] ?? $share->permission,
            'accepted'   => $share->accepted_at !== null,
            'user'       => $share->user ? [
                'name'   => $share->user->name,
                'avatar' => $share->user->avatar,
            ] : null,
        ];
    }

    public function index(MaintenanceProject $maintenanceProject, Request $request)
    {
        return response()->json(
            $maintenanceProject->shares()->with('user')->get()->map(fn ($s) => $this->formatShare($s))
        );
    }

    public function store(MaintenanceProject $maintenanceProject, Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'invites' => 'required|array|min:1',
            'invites.*.email'      => 'required|email',
            'invites.*.permission' => 'required|in:view,comment,edit',
        ]);

        $results  = [];
        $emailed  = [];

        foreach ($validated['invites'] as $invite) {
            $email      = strtolower($invite['email']);
            $dbPerm     = self::PERM_MAP[$invite['permission']];
            $invitedUser = User::where('email', $email)->first();

            if ($user && strtolower($user->email) === $email) {
                continue; // skip self
            }

            $share = MaintenanceProjectShare::updateOrCreate(
                [
                    'maintenance_project_id' => $maintenanceProject->id,
                    'invited_email'          => $email,
                ],
                [
                    'user_id'     => $invitedUser?->id,
                    'invited_by'  => $user?->id,
                    'permission'  => $dbPerm,
                    'accepted_at' => $invitedUser ? now() : null,
                ]
            );

            $share->load('user');
            $results[] = $this->formatShare($share);

            // Send email invitation
            try {
                $inviter = $user ?? (object) ['name' => config('app.name'), 'email' => config('mail.from.address')];
                if ($user) {
                    Mail::to($email)->send(new MaintenanceShareInvite($maintenanceProject, $share, $user));
                    $emailed[] = $email;
                }
            } catch (\Exception $e) {
                \Log::warning("MaintenanceShareInvite failed for {$email}: " . $e->getMessage());
            }
        }

        return response()->json([
            'shares' => $results,
            'emailed' => $emailed,
        ], 201);
    }

    public function update(MaintenanceProject $maintenanceProject, MaintenanceProjectShare $maintenanceProjectShare, Request $request)
    {
        if ($maintenanceProjectShare->maintenance_project_id !== $maintenanceProject->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'permission' => 'required|in:view,comment,edit',
        ]);

        $maintenanceProjectShare->update(['permission' => self::PERM_MAP[$validated['permission']]]);

        return response()->json(['success' => true]);
    }

    public function destroy(MaintenanceProject $maintenanceProject, MaintenanceProjectShare $maintenanceProjectShare, Request $request)
    {
        if ($maintenanceProjectShare->maintenance_project_id !== $maintenanceProject->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $maintenanceProjectShare->delete();

        return response()->json(['message' => 'Share removed']);
    }
}
