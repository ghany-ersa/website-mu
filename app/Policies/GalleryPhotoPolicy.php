<?php

namespace App\Policies;

use App\Models\GalleryPhoto;
use App\Models\Organization;
use App\Models\User;

class GalleryPhotoPolicy
{
    /**
     * Any member of the organization can view its gallery photos.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can add a gallery photo.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit a gallery photo.
     */
    public function update(User $user, GalleryPhoto $galleryPhoto): bool
    {
        return $galleryPhoto->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete a gallery photo.
     */
    public function delete(User $user, GalleryPhoto $galleryPhoto): bool
    {
        return $galleryPhoto->organization->roleFor($user) !== null;
    }
}
