<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BureaucracyReport extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_code',
        'reporter_token_hash',
        'request_id',
        'payload_hash',
        'agency_id',
        'agency_name',
        'region_code',
        'province_name',
        'category',
        'title',
        'chronology',
        'incident_date',
        'asset_identifier',
        'evidence_url',
        'evidence_hash',
        'verification_status',
        'public_resolution_note',
        'verified_by',
        'verified_at',
    ];

    protected $hidden = ['reporter_token_hash', 'payload_hash'];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(PublicAgencyScorecard::class, 'agency_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
