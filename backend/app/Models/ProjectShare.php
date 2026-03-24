<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectShare extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'invited_email', 'invited_by', 'permission', 'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
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
