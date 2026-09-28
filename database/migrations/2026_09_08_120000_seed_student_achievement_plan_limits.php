<?php

use App\Models\Plan;
use App\Models\PlanLimit;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the 'students' and 'achievements' limits to every existing plan. PlanSeeder carries the
 * same numbers for fresh installs, but plan_limits rows already live in an existing database
 * need this migration too - same pattern as the pages_total migration.
 *
 * Pupil profiles are an Eksklusif-only feature: the wall of fame works on any plan (with a
 * small allowance), but the per-pupil pages it links to are part of the exclusive school
 * template, so the lower tiers get 0 and PlanLimitService::canCreate() blocks the CRUD there.
 * Eksklusif's 60/40 comfortably covers the reference school's real data (10 pupils, 8
 * achievements) with room to grow.
 */
return new class extends Migration
{
    public function up(): void
    {
        $limits = [
            'starter' => ['students' => 0, 'achievements' => 3],
            'premium' => ['students' => 0, 'achievements' => 15],
            'eksklusif' => ['students' => 60, 'achievements' => 40],
        ];

        foreach ($limits as $planKey => $keys) {
            $plan = Plan::where('key', $planKey)->first();

            foreach ($keys as $key => $maxCount) {
                $plan?->limits()->updateOrCreate(['key' => $key], ['max_count' => $maxCount]);
            }
        }
    }

    public function down(): void
    {
        PlanLimit::whereIn('key', ['students', 'achievements'])->delete();
    }
};
