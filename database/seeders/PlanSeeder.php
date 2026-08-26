<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the three plans and their limits, mirroring what's actually in the dev database. The
 * numbers below are initial defaults, not a final business decision — adjust via a follow-up
 * seeder once real pricing and usage data are settled. Every component/section is available on
 * every plan; only the total number of sections a plan allows is limited (sections_total).
 *
 * Descriptions only name entitlements that actually exist in code (hide_branding,
 * has_exclusive_templates) — earlier copy referenced custom domains and AI content that were
 * never built, which is the kind of over-promise this plan intentionally avoids repeating.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $starter = Plan::create([
            'key' => 'starter',
            'name' => 'Starter',
            'description' => 'Paket dasar untuk memulai situs organisasi.',
            'price_monthly' => 10000,
        ]);

        $starter->limits()->createMany([
            ['key' => 'posts', 'max_count' => 5],
            ['key' => 'agendas', 'max_count' => 3],
            ['key' => 'announcements', 'max_count' => 2],
            ['key' => 'officers', 'max_count' => 3],
            ['key' => 'programs', 'max_count' => 3],
            ['key' => 'gallery_photos', 'max_count' => 3],
            ['key' => 'sections_total', 'max_count' => 5],
        ]);

        $organization = Plan::create([
            'key' => 'organization',
            'name' => 'Organization',
            'description' => 'Untuk organisasi dengan aktivitas publikasi rutin.',
            'price_monthly' => 18000,
            'hide_branding' => false,
            'has_exclusive_templates' => false,
        ]);

        $organization->limits()->createMany([
            ['key' => 'posts', 'max_count' => 5],
            ['key' => 'agendas', 'max_count' => 4],
            ['key' => 'announcements', 'max_count' => 3],
            ['key' => 'officers', 'max_count' => 7],
            ['key' => 'programs', 'max_count' => 5],
            ['key' => 'gallery_photos', 'max_count' => 8],
            ['key' => 'sections_total', 'max_count' => 8],
        ]);

        $professional = Plan::create([
            'key' => 'professional',
            'name' => 'Professional',
            'description' => 'Kapasitas penuh, tampil tanpa watermark dengan pilihan template eksklusif.',
            'price_monthly' => 25000,
            'hide_branding' => true,
            'has_exclusive_templates' => true,
        ]);

        $professional->limits()->createMany([
            ['key' => 'posts', 'max_count' => 20],
            ['key' => 'agendas', 'max_count' => 10],
            ['key' => 'announcements', 'max_count' => 9],
            ['key' => 'officers', 'max_count' => 12],
            ['key' => 'programs', 'max_count' => 9],
            ['key' => 'gallery_photos', 'max_count' => 40],
            ['key' => 'sections_total', 'max_count' => 25],
        ]);
    }
}
