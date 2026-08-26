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
            'name' => 'Ghany',
            'email' => 'ghany@ghany.id',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            PlanSeeder::class,
            OrganizationTypeSeeder::class,
            TemplateSeeder::class,
        ]);
    }
}
