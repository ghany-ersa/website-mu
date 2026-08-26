<?php

use App\Models\Organization;
use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Backfills plan_id for organizations created before the plan system existed.
     * PlanLimitService::effectivePlan() already treats a null plan_id as equivalent to the
     * 'organization' plan (fail-open, not fail-closed), so this isn't strictly required for
     * limits to work — but an explicit plan_id keeps admin screens, the plan.edit page, and
     * PlanChangeRequestService from displaying "Belum diatur" for every pre-existing tenant.
     */
    public function up(): void
    {
        $organizationPlanId = Plan::where('key', 'organization')->value('id');

        if ($organizationPlanId === null) {
            return;
        }

        Organization::whereNull('plan_id')->update(['plan_id' => $organizationPlanId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally not reversible: we can't distinguish organizations that were null
        // before this migration from ones that were explicitly set to 'organization' after.
    }
};
