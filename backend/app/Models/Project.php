<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description', 'color', 'owner_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function bugs()
    {
        return $this->hasMany(Bug::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shares()
    {
        return $this->hasMany(ProjectShare::class);
    }
}
