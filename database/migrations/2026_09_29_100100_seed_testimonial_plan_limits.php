<?php

use App\Models\Plan;
use App\Models\PlanLimit;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the `testimonials` limit to every existing plan. PlanSeeder carries the same numbers for
 * fresh installs, but plan_limits rows already live in a database need this too - same pattern as
 * the students/achievements migration.
 *
 * A real quota on every tier, because the section is NOT plan-gated - only its editorial
 * `sd-madya` layout is (see config/page-builder.php). At 0 a lower-plan organization could add
 * the section but never put a quote in it: a visible empty state with no way to fill it.
 *
 * Null-safe on the plan lookup, like the migration it mirrors: a database missing one of these
 * keys skips it rather than erroring.
 */
return new class extends Migration
{
    public function up(): void
    {
        $limits = [
            'starter' => 3,
            'premium' => 6,
            'eksklusif' => 12,
        ];

        foreach ($limits as $planKey => $maxCount) {
            Plan::where('key', $planKey)->first()?->limits()->updateOrCreate(
                ['key' => 'testimonials'],
                ['max_count' => $maxCount],
            );
        }
    }

    /**
     * A delete, not a restore: these rows did not exist before this migration, so removing them
     * is the true inverse. PlanLimitService treats a missing key as "no limit", which is what the
     * resource had before it was quota-tracked at all.
     */
    public function down(): void
    {
        PlanLimit::where('key', 'testimonials')->delete();
    }
};
