<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'body', 'visibility', 'status', 'request_id', 'edited_at'])]
class Post extends Model
{
    use HasUuids, SoftDeletes;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'visibility' => PostVisibility::class,
            'status' => PostStatus::class,
            'edited_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Post $post): void {
            if (! $post->isForceDeleting()) {
                $post->status = PostStatus::Deleted;
                $post->saveQuietly();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }
}
