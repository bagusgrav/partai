<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'civic_question_id',
    'repetitions',
    'interval_days',
    'ease_factor',
    'mastery_awarded',
    'last_reviewed_at',
    'next_review_at',
])]
class UserQuestionSchedule extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'repetitions' => 'integer',
            'interval_days' => 'integer',
            'ease_factor' => 'decimal:2',
            'mastery_awarded' => 'boolean',
            'last_reviewed_at' => 'datetime',
            'next_review_at' => 'datetime',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CivicQuestion::class, 'civic_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
