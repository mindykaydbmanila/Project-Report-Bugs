<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    protected $fillable = [
        'maintenance_project_id',
        'sequence',
        'client',
        'request',
        'sent_thru',
        'date_received',
        'target_date',
        'completion_date',
        'assigned_devs',
        'assigned_qa',
        'status',
        'dev_status',
        'notes',
        'comments',
        'attachments',
        'notification_sent_at',
    ];

    protected $casts = [
        'assigned_devs'        => 'array',
        'assigned_qa'          => 'array',
        'comments'             => 'array',
        'attachments'          => 'array',
        'date_received'        => 'date:Y-m-d',
        'target_date'          => 'date:Y-m-d',
        'completion_date'      => 'date:Y-m-d',
        'notification_sent_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(MaintenanceProject::class, 'maintenance_project_id');
    }

    public function getTicketNumberAttribute(): string
    {
        return 'MT-' . str_pad($this->sequence, 3, '0', STR_PAD_LEFT);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['ticket_number'] = $this->ticket_number;
        return $data;
    }
}
