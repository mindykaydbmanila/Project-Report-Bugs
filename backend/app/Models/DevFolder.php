<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevFolder extends Model
{
    protected $fillable = [
        'token',
        'developer_email',
        'developer_name',
        'visibility',
    ];
}
