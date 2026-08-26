<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the two paid plans and their limits/component gates. The numbers below are initial
 * defaults, not a final business decision — adjust via a follow-up seeder once real pricing
 * and usage data are settled.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
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

        $organization->components()->createMany([
            ['component_key' => 'donasi-zakat-infak', 'is_allowed' => false],
            ['component_key' => 'ppdb', 'is_allowed' => false],
            ['component_key' => 'formulir-kontak', 'is_allowed' => false],
            ['component_key' => 'jadwal-praktik', 'is_allowed' => false],
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

        // No plan_components rows: every section, including the 4 gated on 'organization',
        // is allowed by default (opt-out model — see PlanComponent migration).
    }
}
