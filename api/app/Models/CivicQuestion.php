<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'domain',
    'mastery_level',
    'prompt',
    'options',
    'correct_option',
    'explanation',
    'source_citation',
    'source_url',
    'review_status',
    'mastery_points',
])]
class CivicQuestion extends Model
{
    use HasUuids;

    public const MASTERY_LEVELS = [
        1 => 'Kenali',
        2 => 'Pahami',
        3 => 'Terapkan',
        4 => 'Kritisi',
    ];

    public const REVIEW_STATUSES = [
        'draft',
        'under_review',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'mastery_level' => 'integer',
            'options' => 'array',
            'mastery_points' => 'integer',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(UserQuestionSchedule::class);
    }
}
