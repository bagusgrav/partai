<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PartyFinanceEntry extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'fiscal_year' => 'integer',
            'transaction_date' => 'date:Y-m-d',
            'amount_idr' => 'integer',
            'legal_declaration_accepted' => 'boolean',
            'audited_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (): never {
            throw new LogicException('Party finance entries are append-only and cannot be deleted.');
        });

        static::updating(function (PartyFinanceEntry $entry): void {
            $immutable = [
                'sequence',
                'entry_type',
                'channel',
                'amount_idr',
                'identity_hash',
                'previous_hash',
                'hash',
                'request_id',
            ];

            foreach ($immutable as $column) {
                if ($entry->isDirty($column)) {
                    throw new LogicException("Party finance entry column {$column} is immutable.");
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function auditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(PartyFinanceEntry::class, 'reversal_of_id');
    }
}
