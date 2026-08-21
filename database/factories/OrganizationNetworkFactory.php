<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\OrganizationNetwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationNetwork>
 */
class OrganizationNetworkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company(),
            'type' => fake()->randomElement(['AUM Pendidikan', 'AUM Kesehatan', 'Ortom']),
            'order' => 0,
        ];
    }
}
