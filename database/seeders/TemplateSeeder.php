<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Temporarily empty while template/org sample data is being rebuilt from scratch, one
 * organization type at a time - see DatabaseSeeder, which currently only calls
 * KlinikAisyiyahAmbuluTemplateSeeder (AUM Kesehatan). This previously seeded one starter
 * template per organization type (Muhammadiyah, Aisyiyah, every Ortom, AUM Pendidikan/Sosial,
 * Masjid/Mushola, Portal Berita) plus a handful of exclusive templates - reintroduce those
 * here, organization type by organization type, as each is redone with real/representative
 * content the way KlinikAisyiyahAmbuluTemplateSeeder models Klinik Pratama Aisyiyah Ambulu.
 */
class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        //
    }
}
