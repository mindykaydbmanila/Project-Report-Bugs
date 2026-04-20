<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceProjectShare extends Model
{
    protected $fillable = [
        'maintenance_project_id', 'user_id', 'invited_email', 'invited_by', 'permission', 'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(MaintenanceProject::class, 'maintenance_project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
