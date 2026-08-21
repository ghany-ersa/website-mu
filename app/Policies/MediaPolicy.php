<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\Organization;
use App\Models\User;

class MediaPolicy
{
    /**
     * Any member of the organization can view its media library.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can upload media.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete a media item.
     */
    public function delete(User $user, Media $media): bool
    {
        return $media->organization->roleFor($user) !== null;
    }
}
