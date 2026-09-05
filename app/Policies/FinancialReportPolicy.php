<?php

namespace App\Policies;

use App\Models\FinancialReport;
use App\Models\Organization;
use App\Models\User;

class FinancialReportPolicy
{
    /**
     * Any member of the organization can view its financial reports.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the organization can add a financial report entry.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can edit a financial report entry.
     */
    public function update(User $user, FinancialReport $financialReport): bool
    {
        return $financialReport->organization->roleFor($user) !== null;
    }

    /**
     * Any member of the owning organization can delete a financial report entry.
     */
    public function delete(User $user, FinancialReport $financialReport): bool
    {
        return $financialReport->organization->roleFor($user) !== null;
    }
}
