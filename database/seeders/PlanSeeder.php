<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the three plans and their limits, mirroring what's actually in the dev database. The
 * numbers below are initial defaults, not a final business decision — adjust via a follow-up
 * seeder once real pricing and usage data are settled. Every component/section is available on
 * every plan; only the total number of sections a plan allows is limited (sections_total).
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $free = Plan::create([
            'key' => 'free',
            'name' => 'Free',
            'description' => '',
            'price_monthly' => 0,
        ]);

        $free->limits()->createMany([
            ['key' => 'posts', 'max_count' => 0],
            ['key' => 'agendas', 'max_count' => 3],
            ['key' => 'announcements', 'max_count' => 0],
            ['key' => 'officers', 'max_count' => 3],
            ['key' => 'programs', 'max_count' => 3],
            ['key' => 'gallery_photos', 'max_count' => 3],
            ['key' => 'sections_total', 'max_count' => 5],
        ]);

        $organization = Plan::create([
            'key' => 'organization',
            'name' => 'Organization',
            'description' => 'Domain kustom, CMS penuh, tanpa branding platform.',
            'price_monthly' => 9900,
        ]);

        $organization->limits()->createMany([
            ['key' => 'posts', 'max_count' => 5],
            ['key' => 'agendas', 'max_count' => 4],
            ['key' => 'announcements', 'max_count' => 3],
            ['key' => 'officers', 'max_count' => 7],
            ['key' => 'programs', 'max_count' => 4],
            ['key' => 'gallery_photos', 'max_count' => 8],
            ['key' => 'sections_total', 'max_count' => 8],
        ]);

        $professional = Plan::create([
            'key' => 'professional',
            'name' => 'Professional',
            'description' => 'Multi-editor, AI content, analytics lanjutan, komponen lebih banyak.',
            'price_monthly' => 16500,
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
