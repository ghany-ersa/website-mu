<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\OrganizationPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationPage>
 */
class OrganizationPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'organization_id' => Organization::factory(),
            'name' => str($name)->title(),
            'slug' => str($name)->slug(),
            'order' => 0,
            'is_home' => false,
            'published_at' => null,
        ];
    }
}
