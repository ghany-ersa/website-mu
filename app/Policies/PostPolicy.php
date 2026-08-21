<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Any member of the organization can view its posts.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can create a post.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit a post.
     */
    public function update(User $user, Post $post): bool
    {
        return $post->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete a post.
     */
    public function delete(User $user, Post $post): bool
    {
        return $post->organization->roleFor($user) !== null;
    }
}
