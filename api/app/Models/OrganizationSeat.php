<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'seat_code', 'parent_id', 'layer', 'title', 'territory_level', 'region_code',
    'province_name', 'answering_scope', 'selection_method', 'required_mastery_level',
    'required_domain', 'required_min_participation', 'term_duration_months',
    'max_consecutive_terms', 'holder_user_id', 'holder_display_name',
    'term_starts_at', 'term_ends_at', 'status', 'sort_order',
])]
class OrganizationSeat extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'term_starts_at' => 'date',
            'term_ends_at' => 'date',
            'required_mastery_level' => 'integer',
            'required_min_participation' => 'integer',
            'term_duration_months' => 'integer',
            'max_consecutive_terms' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'holder_user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(SeatApplication::class, 'seat_id');
    }
}
