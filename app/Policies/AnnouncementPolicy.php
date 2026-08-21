<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\Organization;
use App\Models\User;

class AnnouncementPolicy
{
    /**
     * Any member of the organization can view its announcements.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can create an announcement.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit an announcement.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        return $announcement->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete an announcement.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        return $announcement->organization->roleFor($user) !== null;
    }
}
