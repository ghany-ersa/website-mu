<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    /**
     * Any member of the organization can view its programs/services.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can add a program/service.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit a program/service.
     */
    public function update(User $user, Program $program): bool
    {
        return $program->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete a program/service.
     */
    public function delete(User $user, Program $program): bool
    {
        return $program->organization->roleFor($user) !== null;
    }
}
