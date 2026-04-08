<?php

namespace App\Mail;

use App\Models\Bug;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BugTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Bug $bug,
        public User $developer,
        public User $assigner,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->bug->priority}] Bug ticket assigned: #{$this->bug->sequence} — {$this->bug->title} ({$this->bug->scenario_type})",
        );
    }

    public function content(): Content
    {
        $appUrl   = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $ticketUrl = "{$appUrl}/ticket/{$this->bug->id}";
        $devFirstName = explode(' ', $this->bug->assignedDeveloper->name ?? $this->developer->name)[0];
        $priority = $this->bug->priority;
        $priorityColor = match($priority) {
            'Critical' => '#dc2626',
            'High'     => '#ea580c',
            'Medium'   => '#ca8a04',
            'Low'      => '#16a34a',
            default    => '#64748b',
        };

        $description = strip_tags($this->bug->description ?? '—');
        $reportedDate = $this->bug->created_at->format('F j, Y');

        return new Content(
            htmlString: <<<HTML
            <!DOCTYPE html>
            <html>
            <body style="font-family:Inter,Arial,sans-serif;background:#f1f5f9;margin:0;padding:32px 16px;">
              <div style="max-width:560px;margin:0 auto;">
                <!-- Header -->
                <div style="background:#4f46e5;border-radius:12px 12px 0 0;padding:24px 32px;">
                  <div style="color:#c7d2fe;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:8px;">BUG TICKET ASSIGNED</div>
                  <div style="color:#fff;font-size:24px;font-weight:800;line-height:1.2;margin-bottom:6px;">#{$this->bug->sequence} — {$this->bug->title}</div>
                  <div style="color:#a5b4fc;font-size:13px;">{$this->bug->scenario_type}</div>
                </div>

                <!-- Body -->
                <div style="background:#fff;border-radius:0 0 12px 12px;padding:32px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
                  <p style="margin:0 0 24px;color:#334155;font-size:15px;">
                    Hi <strong>{$devFirstName}</strong>, a bug ticket has been assigned to you by <strong>{$this->assigner->name}</strong>.
                  </p>

                  <!-- Details table -->
                  <table style="width:100%;border-collapse:collapse;margin-bottom:28px;">
                    <tr style="background:#f8fafc;">
                      <td style="padding:10px 14px;font-size:13px;color:#64748b;font-weight:600;width:40%;border-bottom:1px solid #e2e8f0;">Title</td>
                      <td style="padding:10px 14px;font-size:14px;color:#1e293b;border-bottom:1px solid #e2e8f0;">{$this->bug->title}</td>
                    </tr>
                    <tr>
                      <td style="padding:10px 14px;font-size:13px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Priority</td>
                      <td style="padding:10px 14px;border-bottom:1px solid #e2e8f0;">
                        <span style="background:{$priorityColor}1a;color:{$priorityColor};font-size:12px;font-weight:700;padding:2px 10px;border-radius:99px;">{$priority}</span>
                      </td>
                    </tr>
                    <tr style="background:#f8fafc;">
                      <td style="padding:10px 14px;font-size:13px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Scenario</td>
                      <td style="padding:10px 14px;font-size:14px;color:#1e293b;border-bottom:1px solid #e2e8f0;">{$this->bug->scenario_type}</td>
                    </tr>
                    <tr>
                      <td style="padding:10px 14px;font-size:13px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Status</td>
                      <td style="padding:10px 14px;font-size:14px;color:#1e293b;border-bottom:1px solid #e2e8f0;">{$this->bug->status}</td>
                    </tr>
                    <tr style="background:#f8fafc;">
                      <td style="padding:10px 14px;font-size:13px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Description</td>
                      <td style="padding:10px 14px;font-size:14px;color:#1e293b;border-bottom:1px solid #e2e8f0;">{$description}</td>
                    </tr>
                    <tr>
                      <td style="padding:10px 14px;font-size:13px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Assigned by</td>
                      <td style="padding:10px 14px;font-size:14px;color:#1e293b;border-bottom:1px solid #e2e8f0;">{$this->assigner->name}</td>
                    </tr>
                    <tr style="background:#f8fafc;">
                      <td style="padding:10px 14px;font-size:13px;color:#64748b;font-weight:600;">Reported</td>
                      <td style="padding:10px 14px;font-size:14px;color:#1e293b;">{$reportedDate}</td>
                    </tr>
                  </table>

                  <!-- CTA -->
                  <div style="text-align:center;margin-bottom:24px;">
                    <a href="{$ticketUrl}"
                       style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:15px;font-weight:600;">
                      View &amp; respond to ticket →
                    </a>
                  </div>

                  <p style="color:#94a3b8;font-size:13px;margin:0;text-align:center;line-height:1.6;">
                    From the tracker you can leave comments, update the status,<br>or mark the ticket as resolved.
                  </p>
                </div>
              </div>
            </body>
            </html>
            HTML,
        );
    }
}
