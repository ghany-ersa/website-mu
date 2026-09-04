<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Any authenticated user can start a new organization.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only members of the organization can view its dashboard.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Only members of the organization can edit its details.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Only Owner/Admin members can add, remove, or re-role members.
     */
    public function manageMembers(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user)?->canManageMembers() ?? false;
    }

    /**
     * Only the Owner can delete the organization.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) === \App\Enums\OrganizationRole::Owner;
    }

    /**
     * Only the Owner can view/change the organization's subscription plan - it's a billing
     * decision, unlike the general settings any member can touch via update().
     */
    public function manageBilling(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) === \App\Enums\OrganizationRole::Owner;
    }
}
