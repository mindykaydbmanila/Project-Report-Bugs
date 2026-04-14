<?php

namespace App\Mail;

use App\Models\MaintenanceProject;
use App\Models\MaintenanceTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MaintenanceNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MaintenanceTicket  $ticket,
        public MaintenanceProject $project,
        public string             $recipientEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Maintenance] {$this->ticket->ticket_number} — {$this->ticket->client}: {$this->ticket->request}",
        );
    }

    public function attachments(): array
    {
        $result = [];

        foreach ($this->ticket->attachments ?? [] as $url) {
            // Only attach PDFs — images are already visible in the email body
            if (!preg_match('/\.pdf$/i', $url)) {
                continue;
            }

            // URL is like /storage/maintenance-attachments/file.pdf
            // Strip the leading /storage/ to get the disk-relative path
            $relativePath = ltrim(str_replace('/storage/', '', $url), '/');

            if (Storage::disk('public')->exists($relativePath)) {
                $result[] = Attachment::fromStorageDisk('public', $relativePath)
                    ->as(basename($relativePath))
                    ->withMime('application/pdf');
            }
        }

        return $result;
    }

    public function content(): Content
    {
        $ticket  = $this->ticket;
        $project = $this->project;

        $statusColor = match ($ticket->status) {
            'In Progress' => '#2563eb',
            'On Hold'     => '#d97706',
            'Completed'   => '#16a34a',
            'Cancelled'   => '#64748b',
            default       => '#7c3aed',
        };

        $received    = $ticket->date_received   ? $ticket->date_received->format('F j, Y')   : '—';
        $target      = $ticket->target_date     ? $ticket->target_date->format('F j, Y')     : '—';
        $completion  = $ticket->completion_date ? $ticket->completion_date->format('F j, Y') : '—';
        $devs        = implode(', ', $ticket->assigned_devs ?? []) ?: '—';
        $qa          = implode(', ', $ticket->assigned_qa   ?? []) ?: '—';
        $requestText = htmlspecialchars($ticket->request);
        $notesText   = htmlspecialchars($ticket->notes ?? '');

        $rows = '';

        $rows .= '<tr style="background:#f8fafc;">'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;width:38%;border-bottom:1px solid #e2e8f0;">Ticket #</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;font-weight:700;">' . $ticket->ticket_number . '</td>'
            . '</tr>';

        $rows .= '<tr>'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Client</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;">' . htmlspecialchars($ticket->client) . '</td>'
            . '</tr>';

        $rows .= '<tr style="background:#f8fafc;">'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Request</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;">' . $requestText . '</td>'
            . '</tr>';

        $rows .= '<tr>'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Sent Through</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;">' . htmlspecialchars($ticket->sent_thru) . '</td>'
            . '</tr>';

        $rows .= '<tr style="background:#f8fafc;">'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Status</td>'
            . '<td style="padding:10px 14px;border-bottom:1px solid #e2e8f0;">'
            . '<span style="background:' . $statusColor . '1a;color:' . $statusColor . ';font-size:11px;font-weight:700;padding:2px 10px;border-radius:99px;">' . $ticket->status . '</span>'
            . '</td>'
            . '</tr>';

        $rows .= '<tr>'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Received</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;">' . $received . '</td>'
            . '</tr>';

        $rows .= '<tr style="background:#f8fafc;">'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Target Date</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;">' . $target . '</td>'
            . '</tr>';

        $rows .= '<tr>'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Completion Date</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;">' . $completion . '</td>'
            . '</tr>';

        $rows .= '<tr style="background:#f8fafc;">'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Assigned Dev(s)</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #e2e8f0;">' . htmlspecialchars($devs) . '</td>'
            . '</tr>';

        $rows .= '<tr>'
            . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;' . ($notesText ? 'border-bottom:1px solid #e2e8f0;' : '') . '">Assigned QA</td>'
            . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;' . ($notesText ? 'border-bottom:1px solid #e2e8f0;' : '') . '">' . htmlspecialchars($qa) . '</td>'
            . '</tr>';

        if ($notesText) {
            $rows .= '<tr style="background:#f8fafc;">'
                . '<td style="padding:10px 14px;font-size:12px;color:#64748b;font-weight:600;">Notes</td>'
                . '<td style="padding:10px 14px;font-size:13px;color:#1e293b;">' . $notesText . '</td>'
                . '</tr>';
        }

        $ticketUrl = 'http://localhost:3000/maintenance-ticket/' . $ticket->id;

        $html = '<!DOCTYPE html>'
            . '<html><body style="font-family:Inter,Arial,sans-serif;background:#f1f5f9;margin:0;padding:32px 16px;">'
            . '<div style="max-width:580px;margin:0 auto;">'

            . '<div style="background:linear-gradient(135deg,#064e3b 0%,#065f46 60%,#047857 100%);border-radius:12px 12px 0 0;padding:24px 32px;">'
            . '<div style="color:#a7f3d0;font-size:12px;font-weight:600;letter-spacing:.6px;margin-bottom:6px;text-transform:uppercase;">Maintenance Ticket · ' . htmlspecialchars($project->name) . '</div>'
            . '<div style="color:#fff;font-size:20px;font-weight:700;">' . $ticket->ticket_number . ' — ' . htmlspecialchars($ticket->client) . '</div>'
            . '<div style="color:#d1fae5;font-size:14px;margin-top:4px;">' . $requestText . '</div>'
            . '</div>'

            . '<div style="background:#fff;border-radius:0 0 12px 12px;padding:32px;box-shadow:0 2px 8px rgba(0,0,0,.08);">'
            . '<p style="margin:0 0 24px;color:#334155;font-size:14px;line-height:1.7;">You have been assigned to a maintenance ticket in <strong>' . htmlspecialchars($project->name) . '</strong>. Please review the details below.</p>'
            . '<table style="width:100%;border-collapse:collapse;margin-bottom:28px;">' . $rows . '</table>'

            . '<div style="text-align:center;margin-bottom:28px;">'
            . '<a href="' . $ticketUrl . '" style="display:inline-block;background:linear-gradient(135deg,#059669,#047857);color:#fff;font-size:14px;font-weight:700;padding:12px 32px;border-radius:8px;text-decoration:none;letter-spacing:.3px;">View Ticket →</a>'
            . '<div style="margin-top:10px;font-size:11px;color:#94a3b8;">Or copy this link: <a href="' . $ticketUrl . '" style="color:#059669;">' . $ticketUrl . '</a></div>'
            . '</div>'

            . '<p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;line-height:1.7;">This notification was sent by the QA Maintenance Tracker.<br>Reply to this email or contact your project lead with any questions.</p>'
            . '</div>'

            . '</div>'
            . '</body></html>';

        return new Content(htmlString: $html);
    }
}
