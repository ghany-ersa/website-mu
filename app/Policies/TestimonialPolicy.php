<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $testimonial->organization->roleFor($user) !== null;
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $testimonial->organization->roleFor($user) !== null;
    }
}
