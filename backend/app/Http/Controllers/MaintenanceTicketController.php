<?php

namespace App\Http\Controllers;

use App\Mail\MaintenanceNotificationMail;
use App\Models\MaintenanceProject;
use App\Models\MaintenanceTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaintenanceTicketController extends Controller
{
    public function index(MaintenanceProject $maintenanceProject)
    {
        $tickets = $maintenanceProject->tickets()->orderBy('sequence')->get();
        return response()->json($tickets);
    }

    public function store(Request $request, MaintenanceProject $maintenanceProject)
    {
        $data = $request->validate([
            'client'            => 'required|string|max:255',
            'request'           => 'required|string',
            'sent_thru'         => 'nullable|string|max:50',
            'date_received'     => 'nullable|date',
            'target_date'       => 'nullable|date',
            'completion_date'   => 'nullable|date',
            'assigned_devs'     => 'nullable|array',
            'assigned_devs.*'   => 'email',
            'assigned_qa'       => 'nullable|array',
            'assigned_qa.*'     => 'email',
            'status'            => 'nullable|string|in:Pending,In Progress,On Hold,Completed,Cancelled',
            'notes'             => 'nullable|string',
            'attachments'       => 'nullable|array',
            'attachments.*'     => 'file|max:20480|mimes:jpeg,jpg,png,gif,webp,pdf',
        ]);

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = Storage::url($file->store('maintenance-attachments', 'public'));
            }
        }
        $data['attachments'] = $attachmentPaths;

        $maxSeq = $maintenanceProject->tickets()->max('sequence') ?? 0;
        $data['sequence']               = $maxSeq + 1;
        $data['maintenance_project_id'] = $maintenanceProject->id;
        $data['sent_thru']              = $data['sent_thru'] ?? 'Email';
        $data['status']                 = $data['status'] ?? 'Pending';

        $ticket = MaintenanceTicket::create($data);

        $recipients = array_merge(
            $data['assigned_devs'] ?? [],
            $data['assigned_qa']   ?? []
        );

        if (!empty($recipients)) {
            $this->sendNotifications($ticket, $maintenanceProject, $recipients);
            $ticket->update(['notification_sent_at' => now()]);
            $ticket->refresh();
        }

        return response()->json($ticket, 201);
    }

    public function show(MaintenanceProject $maintenanceProject, MaintenanceTicket $maintenanceTicket)
    {
        abort_unless($maintenanceTicket->maintenance_project_id === $maintenanceProject->id, 404);
        return response()->json($maintenanceTicket);
    }

    public function update(Request $request, MaintenanceProject $maintenanceProject, MaintenanceTicket $maintenanceTicket)
    {
        abort_unless($maintenanceTicket->maintenance_project_id === $maintenanceProject->id, 404);

        $data = $request->validate([
            'client'                    => 'sometimes|required|string|max:255',
            'request'                   => 'sometimes|required|string',
            'sent_thru'                 => 'nullable|string|max:50',
            'date_received'             => 'nullable|date',
            'target_date'               => 'nullable|date',
            'completion_date'           => 'nullable|date',
            'assigned_devs'             => 'nullable|array',
            'assigned_devs.*'           => 'email',
            'assigned_qa'               => 'nullable|array',
            'assigned_qa.*'             => 'email',
            'status'                    => 'nullable|string|in:Pending,In Progress,On Hold,Completed,Cancelled',
            'notes'                     => 'nullable|string',
            'existing_attachments'      => 'nullable|array',
            'existing_attachments.*'    => 'string',
            'new_attachments'           => 'nullable|array',
            'new_attachments.*'         => 'file|max:20480|mimes:jpeg,jpg,png,gif,webp,pdf',
        ]);

        // Build final attachments list: kept existing + newly uploaded
        $kept = $data['existing_attachments'] ?? ($maintenanceTicket->attachments ?? []);
        unset($data['existing_attachments']);

        // Delete removed attachment files from storage
        foreach (array_diff($maintenanceTicket->attachments ?? [], $kept) as $url) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }

        $newPaths = [];
        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $file) {
                $newPaths[] = Storage::url($file->store('maintenance-attachments', 'public'));
            }
        }

        $data['attachments'] = array_merge($kept, $newPaths);
        unset($data['new_attachments']);

        $maintenanceTicket->update($data);
        return response()->json($maintenanceTicket->fresh());
    }

    public function destroy(MaintenanceProject $maintenanceProject, MaintenanceTicket $maintenanceTicket)
    {
        abort_unless($maintenanceTicket->maintenance_project_id === $maintenanceProject->id, 404);

        foreach ($maintenanceTicket->attachments ?? [] as $url) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }

        $maintenanceTicket->delete();
        return response()->json(null, 204);
    }

    public function notify(MaintenanceProject $maintenanceProject, MaintenanceTicket $maintenanceTicket)
    {
        abort_unless($maintenanceTicket->maintenance_project_id === $maintenanceProject->id, 404);

        $recipients = array_unique(array_merge(
            $maintenanceTicket->assigned_devs ?? [],
            $maintenanceTicket->assigned_qa   ?? []
        ));

        if (empty($recipients)) {
            return response()->json(['error' => 'No recipients assigned.'], 422);
        }

        $this->sendNotifications($maintenanceTicket, $maintenanceProject, $recipients);
        $maintenanceTicket->update(['notification_sent_at' => now()]);

        return response()->json([
            'sent_at'    => $maintenanceTicket->fresh()->notification_sent_at,
            'recipients' => $recipients,
        ]);
    }

    private function sendNotifications(MaintenanceTicket $ticket, MaintenanceProject $project, array $emails): void
    {
        foreach ($emails as $email) {
            try {
                Mail::to($email)->queue(new MaintenanceNotificationMail($ticket, $project, $email));
            } catch (\Exception $e) {
                \Log::warning("Failed to send maintenance notification to {$email}: " . $e->getMessage());
            }
        }
    }
}
