<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Raises 'sections_total' on the two non-exclusive plans: starter 5 -> 10, organization
     * 8 -> 15. Professional (25) is untouched.
     *
     * The old numbers were set before any standard-tier template existed, and turned out to be
     * the binding constraint on how complete a single-page profile could be - a cabang profile
     * needs roughly eight sections just to cover identity, structure, programs, agenda, news,
     * network, and contact (see PcmAmbuluTemplateSeeder), which left Starter unable to seed the
     * back half of its own template at all: Organization::seedPagesFromTemplate() silently
     * drops sections past the limit, so the owner never saw them. Ten and fifteen leave real
     * headroom to ADD sections to a seeded template rather than only just fitting it.
     *
     * Only raises, never lowers: an organization already over a limit is in violation (see
     * Organization::planViolations()), so this migration must not be the thing that puts one
     * there. PlanSeeder is updated in step for fresh installs; plan_limits rows already live in
     * every existing database need this migration too.
     */
    public function up(): void
    {
        $limits = [
            'starter' => 10,
            'organization' => 15,
        ];

        foreach ($limits as $key => $maxCount) {
            Plan::where('key', $key)->first()?->limits()->updateOrCreate(
                ['key' => 'sections_total'],
                ['max_count' => $maxCount],
            );
        }
    }

    /**
     * Restores the previous caps. Deliberately NOT a delete (unlike the migration that first
     * introduced pages_total): these rows predate this change, so dropping them would leave the
     * plans with no sections_total limit at all - i.e. unlimited, the opposite of a rollback.
     */
    public function down(): void
    {
        $limits = [
            'starter' => 5,
            'organization' => 8,
        ];

        foreach ($limits as $key => $maxCount) {
            Plan::where('key', $key)->first()?->limits()->updateOrCreate(
                ['key' => 'sections_total'],
                ['max_count' => $maxCount],
            );
        }
    }
};
