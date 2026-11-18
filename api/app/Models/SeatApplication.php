<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'seat_id', 'user_id', 'request_id', 'statement',
    'mastery_level_snapshot', 'participation_score_snapshot', 'status',
])]
class SeatApplication extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'mastery_level_snapshot' => 'integer',
            'participation_score_snapshot' => 'integer',
        ];
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(OrganizationSeat::class, 'seat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
