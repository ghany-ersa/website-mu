<?php

namespace App\Policies;

use App\Models\DonationProgram;
use App\Models\Organization;
use App\Models\User;

class DonationProgramPolicy
{
    /**
     * Any member of the organization can view its donation programs.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can add a donation program.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit a donation program (including
     * recording transactions against it).
     */
    public function update(User $user, DonationProgram $donationProgram): bool
    {
        return $donationProgram->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete a donation program.
     */
    public function delete(User $user, DonationProgram $donationProgram): bool
    {
        return $donationProgram->organization->roleFor($user) !== null;
    }
}
