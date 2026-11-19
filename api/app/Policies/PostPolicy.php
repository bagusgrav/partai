<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function create(User $user): bool
    {
        return $user->role !== 'guest';
    }

    public function update(User $user, Post $post): bool
    {
        return $post->user_id === $user->getKey();
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->isModerator($user) || $post->user_id === $user->getKey();
    }

    public function hide(User $user, Post $post): bool
    {
        return $this->isModerator($user);
    }

    private function isModerator(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'pengurus']);
    }
}
