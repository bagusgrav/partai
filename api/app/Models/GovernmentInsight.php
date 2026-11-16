<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentInsight extends Model
{
    protected $fillable = [
        'category',
        'title',
        'content',
        'source_url',
        'image_url',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
