<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'politician_id',
    'politician_name',
    'title',
    'description',
    'status',
    'evidence_url',
    'deadline',
])]
class FactionContract extends Model
{
    use HasUuids;

    public const STATUSES = [
        'BELUM_DIMULAI',
        'SEDANG_BERJALAN',
        'TERPENUHI',
        'GAGAL',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date:Y-m-d',
        ];
    }

    public function politician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'politician_id');
    }
}
