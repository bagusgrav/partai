<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecallRequest extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(RecallVote::class);
    }
}
