<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Sets 'sections_total' on the two non-exclusive plans: starter 5 -> 8, organization 8 -> 15.
     * Professional (25) is untouched.
     *
     * The old numbers were set before any standard-tier template existed, and turned out to be
     * the binding constraint on how complete a single-page profile could be - a cabang profile
     * needs several sections just to cover identity, structure, programs, agenda, news, and
     * contact, which left Starter unable to seed the back half of its own template at all:
     * Organization::seedPagesFromTemplate() silently drops sections past the limit, so the owner
     * never saw them. Every standard-tier template (Cabang Muhammadiyah, Cabang Aisyiyah, Klinik
     * & Rumah Sakit, Masjid & Mushola, Portal Berita Organisasi) is written to exactly 7-8
     * unlocked sections to match, so a Starter organization always gets its complete template,
     * untruncated.
     *
     * Force-set rather than staged as raise-then-lower: no production data exists yet for this
     * app, so there's no existing organization whose plan_violations() this could trip - the
     * usual reason to raise a limit before lowering it in a separate, careful migration doesn't
     * apply here. PlanSeeder is updated in step for fresh installs; plan_limits rows already
     * live in any dev/staging database need this migration too.
     */
    public function up(): void
    {
        $limits = [
            'starter' => 8,
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
