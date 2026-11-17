<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'mastery_scores',
    'overall_mastery_level',
    'participation_score',
    'eligible_roles',
])]
class MemberReputation extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'mastery_scores' => 'array',
            'overall_mastery_level' => 'integer',
            'participation_score' => 'integer',
            'eligible_roles' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(ReputationLedger::class, 'user_id', 'user_id');
    }
}
