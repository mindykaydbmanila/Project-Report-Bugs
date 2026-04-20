<?php

namespace App\Mail;

use App\Models\MaintenanceProject;
use App\Models\MaintenanceProjectShare;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceShareInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MaintenanceProject $project,
        public MaintenanceProjectShare $share,
        public User $inviter,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->inviter->name} shared a maintenance project with you — {$this->project->name}",
        );
    }

    public function content(): Content
    {
        $permissionLabel = match ($this->share->permission) {
            'editor'    => 'Editor (can create, edit, and delete tickets)',
            'commenter' => 'Commenter (can view and comment on tickets)',
            default     => 'Viewer (can view tickets only)',
        };

        $appUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));

        return new Content(
            htmlString: <<<HTML
            <!DOCTYPE html>
            <html>
            <body style="font-family:Inter,Arial,sans-serif;background:#f8fafc;margin:0;padding:32px;">
              <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 1px 4px rgba(0,0,0,.08);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                  <div style="width:40px;height:40px;border-radius:10px;background:#4f46e520;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                  </div>
                  <span style="font-size:16px;font-weight:700;color:#1e293b;">Maintenance Tracker</span>
                </div>
                <h2 style="margin:0 0 8px;color:#1e293b;font-size:20px;">You've been invited to a project</h2>
                <p style="color:#475569;margin:0 0 16px;font-size:15px;">
                  <strong>{$this->inviter->name}</strong> has shared the maintenance project
                  <strong>&ldquo;{$this->project->name}&rdquo;</strong> with you.
                </p>
                <div style="background:#f1f5f9;border-radius:8px;padding:12px 16px;margin-bottom:24px;">
                  <span style="font-size:13px;color:#64748b;">Your access level:</span><br>
                  <span style="font-size:14px;font-weight:600;color:#1e293b;">{$permissionLabel}</span>
                </div>
                <a href="{$appUrl}/maintenance-shared?project={$this->project->id}"
                   style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;">
                  Open Project
                </a>
                <p style="color:#94a3b8;font-size:13px;margin:24px 0 0;">
                  Sign in with the Google account associated with this email address to access the project.
                </p>
              </div>
            </body>
            </html>
            HTML,
        );
    }
}
