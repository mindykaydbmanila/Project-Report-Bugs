<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceProject extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'contract_start',
        'contract_end',
        'owner_id',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'contract_start' => 'date:Y-m-d',
        'contract_end'   => 'date:Y-m-d',
    ];

    public function tickets()
    {
        return $this->hasMany(MaintenanceTicket::class);
    }
}
