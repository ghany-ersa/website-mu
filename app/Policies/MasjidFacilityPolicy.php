<?php

namespace App\Policies;

use App\Models\MasjidFacility;
use App\Models\Organization;
use App\Models\User;

class MasjidFacilityPolicy
{
    /**
     * Any member of the organization can view its facilities.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can add a facility.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit a facility.
     */
    public function update(User $user, MasjidFacility $masjidFacility): bool
    {
        return $masjidFacility->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete a facility.
     */
    public function delete(User $user, MasjidFacility $masjidFacility): bool
    {
        return $masjidFacility->organization->roleFor($user) !== null;
    }
}
