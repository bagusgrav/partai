<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'question',
    'answer',
    'is_published',
])]
class Faq extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}
