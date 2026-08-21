<?php

namespace App\Policies;

use App\Models\Officer;
use App\Models\Organization;
use App\Models\User;

class OfficerPolicy
{
    /**
     * Any member of the organization can view its officers.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can add an officer.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit an officer.
     */
    public function update(User $user, Officer $officer): bool
    {
        return $officer->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete an officer.
     */
    public function delete(User $user, Officer $officer): bool
    {
        return $officer->organization->roleFor($user) !== null;
    }
}
