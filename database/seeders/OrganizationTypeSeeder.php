<?php

namespace Database\Seeders;

use App\Enums\OrganizationCategory;
use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class OrganizationTypeSeeder extends Seeder
{
    /**
     * Seed the organization type list - currently only types that already have a matching
     * seeded Template (see PcmAmbuluTemplateSeeder, PcaAmbuluTemplateSeeder,
     * KlinikAisyiyahAmbuluTemplateSeeder, SuaraMuhammadiyahAmbuluTemplateSeeder), while
     * template/org sample data is rebuilt from scratch. Add a type back here once its template
     * exists, so organizations can't be created against a type with nothing to seed their pages
     * from.
     *
     * Types name the MOVEMENT or the INSTITUTION, never the tier or the category: 'Muhammadiyah'
     * rather than 'Pimpinan Cabang Muhammadiyah' (one type serves PDM/PCM/PRM alike, and a
     * ranting shouldn't have to pick a type labelled "cabang"), and 'Klinik/Rumah Sakit' rather
     * than 'AUM Kesehatan' (an organization signing up recognizes what it IS faster than which
     * AUM category it falls under).
     *
     * Renaming an entry here changes its SLUG, which is how every template seeder looks its type
     * up - and those lookups are null-safe (`$organizationType?->id`), so a stale slug fails
     * SILENTLY, seeding the template with no type at all rather than erroring. Update both sides
     * together. Existing rows are matched by slug too, so a rename creates a NEW row and leaves
     * the old one behind on any database that isn't rebuilt from scratch.
     */
    public function run(): void
    {
        $types = [
            [OrganizationCategory::Organisasi, [
                'Muhammadiyah' => 'Muhammadiyah',
                'Aisyiyah' => 'Aisyiyah',
            ]],
            [OrganizationCategory::Aum, [
                'Klinik/Rumah Sakit' => 'Klinik/Rumah Sakit',
                'Media/Portal Berita' => 'Media/Portal Berita',
            ]],
        ];

        foreach ($types as [$category, $names]) {
            foreach ($names as $name => $description) {
                OrganizationType::updateOrCreate(
                    ['slug' => str($name)->slug()],
                    [
                        'category' => $category,
                        'name' => $name,
                        'description' => $description,
                    ],
                );
            }
        }
    }
}
