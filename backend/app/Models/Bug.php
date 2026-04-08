<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bug extends Model
{
    protected $fillable = [
        'project_id',
        'sequence',
        'title',
        'subtitles',
        'description',
        'priority',
        'scenario_type',
        'status',
        'images',
        'frontend_images',
        'cms_images',
        'developer_comment',
        'dev_comments',
        'assigned_developer_id',
        'guest_developer_email',
        'guest_developer_name',
        'assigned_developers',   // new: JSON array of {id,name,email,avatar}
        'dev_status',
        'date_to_accomplish',
        'resolved_by',
        'ticket_sent_at',
        'activity_log',
    ];

    protected $casts = [
        'images'               => 'array',
        'subtitles'            => 'array',
        'frontend_images'      => 'array',
        'cms_images'           => 'array',
        'dev_comments'         => 'array',
        'activity_log'         => 'array',
        'assigned_developers'  => 'array',
        'date_to_accomplish'   => 'date:Y-m-d',
        'ticket_sent_at'       => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedDeveloper()
    {
        return $this->belongsTo(User::class, 'assigned_developer_id');
    }

    /**
     * Override toArray to expose normalised developer fields consumed by the frontend:
     *   assigned_developers  → full array [{id,name,email,avatar}]
     *   assigned_developer   → first developer (backward compat)
     *   has_assignment       → bool shortcut
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $devs = $this->assigned_developers ?? [];

        $data['assigned_developers'] = $devs;
        $data['assigned_developer']  = !empty($devs) ? $devs[0] : null;
        $data['has_assignment']      = !empty($devs);

        return $data;
    }
}
