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
    ];

    protected $casts = [
        'images'          => 'array',
        'subtitles'       => 'array',
        'frontend_images' => 'array',
        'cms_images'      => 'array',
        'dev_comments'    => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
