<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
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
     * The page builder (structure: pages, sections, layout/variant, reorder) is locked while
     * the site is Published - a member has to unpublish (Draft) first to restructure it, then
     * publish again when done. This is deliberately narrower than update(): the CMS (posts,
     * agenda, officers, ...) stays editable on a live site regardless of this gate, since
     * publishing content is the normal day-to-day flow and shouldn't require going offline.
     * Restructuring the layout itself is the disruptive kind of change that benefits from a
     * draft/review step before it goes live - see OrganizationBuilderController and
     * OrganizationSectionController/OrganizationPageController, which all check this instead
     * of update().
     */
    public function manageBuilder(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null
            && $organization->status !== OrganizationStatus::Published;
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
        return $organization->roleFor($user) === OrganizationRole::Owner;
    }

    /**
     * Only the Owner can view/change the organization's subscription plan - it's a billing
     * decision, unlike the general settings any member can touch via update().
     */
    public function manageBilling(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) === OrganizationRole::Owner;
    }
}
