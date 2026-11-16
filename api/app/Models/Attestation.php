<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'claim_id',
    'attester_id',
    'cluster_id',
    'status',
    'request_id',
])]
class Attestation extends Model
{
    use HasUuids;

    public function claim(): BelongsTo
    {
        return $this->belongsTo(AttendanceClaim::class, 'claim_id');
    }

    public function attester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attester_id');
    }
}
