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
     * KlinikAisyiyahAmbuluTemplateSeeder,
     * SuaraMuhammadiyahAmbuluTemplateSeeder), while template/org sample data is rebuilt from
     * scratch. Add a type back here once its template exists, so organizations can't be created
     * against a type with nothing to seed their pages from.
     */
    public function run(): void
    {
        $types = [
            [OrganizationCategory::Persyarikatan, [
                'Pimpinan Cabang Muhammadiyah' => 'Pimpinan Cabang Muhammadiyah',
            ]],
            // Aisyiyah is an Ortom (organisasi otonom) of Muhammadiyah, not persyarikatan
            // itself - see OrganizationCategory.
            [OrganizationCategory::Ortom, [
                'Pimpinan Cabang Aisyiyah' => 'Pimpinan Cabang Aisyiyah',
            ]],
            [OrganizationCategory::Aum, [
                'AUM Kesehatan' => 'AUM Kesehatan',
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
