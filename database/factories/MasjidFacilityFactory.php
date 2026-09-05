<?php

namespace Database\Factories;

use App\Models\MasjidFacility;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasjidFacility>
 */
class MasjidFacilityFactory extends Factory
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
            'name' => fake()->words(3, true),
            'photo' => fake()->imageUrl(),
            'description' => null,
            'order' => 0,
        ];
    }
}
