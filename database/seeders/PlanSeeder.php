<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the three plans and their limits, mirroring what's actually in the dev database. The
 * numbers below are initial defaults, not a final business decision - adjust via a follow-up
 * seeder once real pricing and usage data are settled. Every component/section is available on
 * every plan; sections_total limits the total sections a plan allows, and pages_total limits
 * how many pages a plan allows (starter/premium are single-page; multi-page and the
 * builder's page switcher are Eksklusif-only).
 *
 * Descriptions only name entitlements that actually exist in code (hide_branding,
 * has_exclusive_templates) - earlier copy referenced custom domains and AI content that were
 * never built, which is the kind of over-promise this plan intentionally avoids repeating.
 *
 * Pricing is a deliberate asymmetric-dominance (decoy) structure, not three independent
 * numbers: Premium sits close to Eksklusif in price (Rp29.000 vs Rp34.000/bulan, a ~17% gap)
 * while carrying none of Eksklusif's entitlements (hide_branding, has_exclusive_templates) -
 * its job is to make Eksklusif look like the obviously better deal, not to sell well on its
 * own. Starter stays low (Rp15.000) as a low-friction entry point for price-sensitive orgs,
 * not a tier meant to compete with Premium/Eksklusif on features.
 *
 * discount_percent_6/12 escalate with the tier - Starter 5%/7%, Premium 7%/10%, Eksklusif
 * 10%/15% - rather than being flat or plan-uniform. This doesn't invert Premium vs Eksklusif
 * (a proportional discount can't flip which of two prices is larger - see
 * Plan::priceForDuration()), but it does narrow the gap the longer someone commits: the
 * Premium->Eksklusif price gap shrinks from 17.2% (3 months, no discount) to 13.5% (6 months)
 * to 10.7% (12 months, eff. Rp26.100 vs Rp28.900/bulan) - so the pitch becomes "the longer
 * you're committing anyway, the smaller the reason to stop at Premium".
 *
 * For the decoy to work, every content limit must actually increase Starter -> Premium ->
 * Eksklusif - a tier that costs more but grants the same quota as the one below it gives a
 * paying org zero reason to have upgraded. pages_total is the one deliberate exception:
 * starter/premium are both capped at 1 (single-page), since multi-page and the builder's page
 * switcher are gated to Eksklusif specifically, not scaled gradually like the CMS content
 * limits.
 *
 * The six image-bearing resources (posts.image, agendas.poster, officers.photo,
 * gallery_photos, facilities.photo, donation_programs.cover_photo - one upload per record)
 * are deliberately picked so each resource's own Starter->Premium ratio roughly matches its
 * Premium->Eksklusif ratio (e.g. gallery_photos 5/10/20 is 2x then 2x) - a flat, predictable
 * "each tier is about double the last" story, rather than a jump that's steeper at one end
 * than the other. donation_programs (1/3/10) is the one exception, since 1 is already the
 * practical floor for a resource to exist at all on Starter.
 *
 * 'posts' is the one resource key in PlanLimitService::MONTHLY_RESOURCES: its max_count
 * (4/7/12) is a per-calendar-month quota, not a lifetime cap like every other key here - an
 * org can publish that many *new* posts each month regardless of how many it already has.
 * Kept in the same 2x-ish per-step growth pattern as the other resources for the same
 * "doubling feels predictable" reason, just resetting monthly instead of counting forever.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $starter = Plan::create([
            'key' => 'starter',
            'name' => 'Starter',
            'description' => 'Paket dasar untuk memulai situs organisasi.',
            'price_monthly' => 15000,
            'discount_percent_6' => 5,
            'discount_percent_12' => 7,
        ]);

        $starter->limits()->createMany([
            ['key' => 'posts', 'max_count' => 4],
            ['key' => 'agendas', 'max_count' => 5],
            ['key' => 'announcements', 'max_count' => 2],
            ['key' => 'officers', 'max_count' => 5],
            ['key' => 'programs', 'max_count' => 3],
            ['key' => 'gallery_photos', 'max_count' => 5],
            ['key' => 'facilities', 'max_count' => 5],
            ['key' => 'donation_programs', 'max_count' => 1],
            ['key' => 'sections_total', 'max_count' => 8],
            ['key' => 'pages_total', 'max_count' => 1],
        ]);

        $premium = Plan::create([
            'key' => 'premium',
            'name' => 'Premium',
            'description' => 'Untuk organisasi dengan aktivitas publikasi rutin.',
            'price_monthly' => 29000,
            'discount_percent_6' => 7,
            'discount_percent_12' => 10,
            'hide_branding' => false,
            'has_exclusive_templates' => false,
        ]);

        $premium->limits()->createMany([
            ['key' => 'posts', 'max_count' => 7],
            ['key' => 'agendas', 'max_count' => 7],
            ['key' => 'announcements', 'max_count' => 3],
            ['key' => 'officers', 'max_count' => 10],
            ['key' => 'programs', 'max_count' => 5],
            ['key' => 'gallery_photos', 'max_count' => 10],
            ['key' => 'facilities', 'max_count' => 10],
            ['key' => 'donation_programs', 'max_count' => 3],
            ['key' => 'sections_total', 'max_count' => 15],
            ['key' => 'pages_total', 'max_count' => 1],
        ]);

        $eksklusif = Plan::create([
            'key' => 'eksklusif',
            'name' => 'Eksklusif',
            'description' => 'Kapasitas penuh, tampil tanpa watermark dengan pilihan template eksklusif.',
            'price_monthly' => 34000,
            'discount_percent_6' => 10,
            'discount_percent_12' => 15,
            'hide_branding' => true,
            'has_exclusive_templates' => true,
        ]);

        $eksklusif->limits()->createMany([
            ['key' => 'posts', 'max_count' => 12],
            ['key' => 'agendas', 'max_count' => 10],
            ['key' => 'announcements', 'max_count' => 9],
            ['key' => 'officers', 'max_count' => 25],
            ['key' => 'programs', 'max_count' => 9],
            ['key' => 'gallery_photos', 'max_count' => 20],
            ['key' => 'facilities', 'max_count' => 15],
            ['key' => 'donation_programs', 'max_count' => 10],
            ['key' => 'sections_total', 'max_count' => 25],
            ['key' => 'pages_total', 'max_count' => 10],
        ]);
    }
}
