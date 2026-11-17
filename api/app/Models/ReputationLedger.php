<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable([
    'user_id',
    'actor_id',
    'sequence',
    'score_type',
    'domain',
    'category',
    'source_type',
    'source_id',
    'delta',
    'reason',
    'request_id',
    'previous_hash',
    'hash',
])]
class ReputationLedger extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'delta' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Reputation ledger entries are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Reputation ledger entries are append-only.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
