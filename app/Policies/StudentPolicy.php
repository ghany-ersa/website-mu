<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    public function update(User $user, Student $student): bool
    {
        return $student->organization->roleFor($user) !== null;
    }

    public function delete(User $user, Student $student): bool
    {
        return $student->organization->roleFor($user) !== null;
    }
}
