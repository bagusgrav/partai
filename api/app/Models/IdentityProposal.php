<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentityProposal extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'proposed_name',
        'proposed_color',
        'proposed_logo_desc',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
