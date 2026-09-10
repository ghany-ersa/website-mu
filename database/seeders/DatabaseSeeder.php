<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->admin()->create([
            'name' => 'Admin Website-Mu',
            'email' => 'admin@website-mu.id',
            'password' => bcrypt('Passwordmu123!'),
        ]);

        $this->call([
            PlanSeeder::class,
            OrganizationTypeSeeder::class,
            SectionVariantSeeder::class,
            // Two templates per organization type: one standard, one exclusive, both built
            // from the same Samples class so each type's pair shows one identical organization
            // at two tiers. See PcmAmbuluEksklusifTemplateSeeder's doc comment.
            PcmAmbuluTemplateSeeder::class,
            PcmAmbuluEksklusifTemplateSeeder::class,
            PcaAmbuluTemplateSeeder::class,
            PcaAmbuluEksklusifTemplateSeeder::class,
            KlinikAisyiyahAmbuluTemplateSeeder::class,
            KlinikAisyiyahAmbuluStandarTemplateSeeder::class,
            SuaraMuhammadiyahAmbuluTemplateSeeder::class,
            SuaraMuhammadiyahAmbuluStandarTemplateSeeder::class,
            // OrganizationSeeder::class,
            ArticleSeeder::class,
        ]);
    }
}
