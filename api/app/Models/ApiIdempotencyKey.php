<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'request_id',
    'endpoint',
    'payload_hash',
    'response_status',
    'response_body',
])]
class ApiIdempotencyKey extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
        ];
    }
}
