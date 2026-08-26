<?php

namespace App\Observers;

use App\Models\Organization;
use App\Services\PlanLimitService;

class OrganizationObserver
{
    public function __construct(private readonly PlanLimitService $planLimitService) {}

    /**
     * Re-syncs section visibility whenever plan_id changes, so upgrading/downgrading a plan
     * immediately reflects in which sections are hidden — see PlanLimitService::reevaluateSections().
     */
    public function updated(Organization $organization): void
    {
        if ($organization->wasChanged('plan_id')) {
            $this->planLimitService->reevaluateSections($organization);
        }
    }
}
