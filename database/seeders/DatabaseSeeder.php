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
            // TemplateSeeder and MasjidNurulHudaTemplateSeeder are temporarily out of the
            // seeding chain while template/org sample data is rebuilt from scratch, starting
            // with AUM Kesehatan (KlinikAisyiyahAmbuluTemplateSeeder). Both files still exist
            // in database/seeders/ and can be added back once their own content is redone.
            KlinikAisyiyahAmbuluTemplateSeeder::class,
            // OrganizationSeeder::class,
            ArticleSeeder::class,
        ]);
    }
}
