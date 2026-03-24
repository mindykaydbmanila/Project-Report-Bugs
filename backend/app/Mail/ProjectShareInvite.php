<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectShareInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public ProjectShare $share,
        public User $inviter,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->inviter->name} shared a project with you — {$this->project->name}",
        );
    }

    public function content(): Content
    {
        $permission = ucfirst($this->share->permission);
        $appUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));

        return new Content(
            htmlString: <<<HTML
            <!DOCTYPE html>
            <html>
            <body style="font-family:Inter,Arial,sans-serif;background:#f8fafc;margin:0;padding:32px;">
              <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 1px 4px rgba(0,0,0,.08);">
                <h2 style="margin:0 0 8px;color:#1e293b;font-size:20px;">You've been invited</h2>
                <p style="color:#475569;margin:0 0 24px;font-size:15px;">
                  <strong>{$this->inviter->name}</strong> has shared the project
                  <strong>&ldquo;{$this->project->name}&rdquo;</strong> with you as a
                  <strong>{$permission}</strong>.
                </p>
                <a href="{$appUrl}"
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
