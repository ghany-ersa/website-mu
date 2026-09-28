<?php

use App\Models\Plan;
use App\Models\PlanLimit;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the `testimonials` limit to every existing plan. PlanSeeder carries the same numbers for
 * fresh installs, but plan_limits rows already live in a database need this too - same pattern as
 * the students/achievements migration.
 *
 * 0 below Eksklusif because the `testimoni` section is itself exclusive (config/page-builder.php),
 * so a lower-plan organization can never add it: a quota above 0 would be a promise the section
 * gate never lets it use, and PlanLimitService::canCreate() would allow a record the page has
 * nowhere to render.
 *
 * Null-safe on the plan lookup, like the migration it mirrors: a database missing one of these
 * keys skips it rather than erroring.
 */
return new class extends Migration
{
    public function up(): void
    {
        $limits = [
            'starter' => 0,
            'premium' => 0,
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
