<?php

namespace App\Policies;

use App\Models\Achievement;
use App\Models\Organization;
use App\Models\User;

class AchievementPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    public function update(User $user, Achievement $achievement): bool
    {
        return $achievement->organization->roleFor($user) !== null;
    }

    public function delete(User $user, Achievement $achievement): bool
    {
        return $achievement->organization->roleFor($user) !== null;
    }
}
