<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationNetwork;
use App\Models\User;

class OrganizationNetworkPolicy
{
    /**
     * Any member of the organization can view its affiliated AUM/Ortom entries.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can add an affiliated AUM/Ortom entry.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit an affiliated AUM/Ortom entry.
     */
    public function update(User $user, OrganizationNetwork $organizationNetwork): bool
    {
        return $organizationNetwork->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete an affiliated AUM/Ortom entry.
     */
    public function delete(User $user, OrganizationNetwork $organizationNetwork): bool
    {
        return $organizationNetwork->organization->roleFor($user) !== null;
    }
}
