<?php

namespace Database\Seeders;

use App\Enums\OrganizationCategory;
use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class OrganizationTypeSeeder extends Seeder
{
    /**
     * Seed the simplified organization type list.
     */
    public function run(): void
    {
        $types = [
            [OrganizationCategory::Persyarikatan, [
                'Muhammadiyah' => 'Muhammadiyah',
            ]],
            [OrganizationCategory::Ortom, [
                'Aisyiyah' => 'Aisyiyah',
                'Pemuda Muhammadiyah' => 'Pemuda Muhammadiyah',
                'Nasyiatul Aisyiyah' => 'Nasyiatul Aisyiyah',
                'Hizbul Wathan' => 'Hizbul Wathan',
                'IPM' => 'Ikatan Pelajar Muhammadiyah',
                'IMM' => 'Ikatan Mahasiswa Muhammadiyah',
                'Tapak Suci' => 'Tapak Suci',
            ]],
            [OrganizationCategory::Aum, [
                'AUM Kesehatan' => 'AUM Kesehatan',
                'AUM Pendidikan' => 'AUM Pendidikan',
                'AUM Sosial' => 'AUM Sosial',
                'Masjid/Mushola' => 'Masjid/Mushola',
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
