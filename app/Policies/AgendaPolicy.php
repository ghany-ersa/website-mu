<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\Organization;
use App\Models\User;

class AgendaPolicy
{
    /**
     * Any member of the organization can view its agenda items.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can create an agenda item.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit an agenda item.
     */
    public function update(User $user, Agenda $agenda): bool
    {
        return $agenda->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete an agenda item.
     */
    public function delete(User $user, Agenda $agenda): bool
    {
        return $agenda->organization->roleFor($user) !== null;
    }
}
