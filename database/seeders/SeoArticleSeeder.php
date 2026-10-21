<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * One command for every landing-page article cluster, run by hand in production:
 *   php artisan db:seed --class=SeoArticleSeeder --force
 * Safe to re-run: each cluster seeder skips slugs that already exist.
 */
class SeoArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MasjidSeoArticleSeeder::class,
            SekolahSeoArticleSeeder::class,
            KlinikSeoArticleSeeder::class,
        ]);
    }
}
