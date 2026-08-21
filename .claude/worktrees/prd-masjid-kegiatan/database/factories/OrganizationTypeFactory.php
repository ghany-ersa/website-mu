<?php

namespace Database\Factories;

use App\Enums\OrganizationCategory;
use App\Models\OrganizationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationType>
 */
class OrganizationTypeFactory extends Factory
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
            'category' => fake()->randomElement(OrganizationCategory::cases()),
            'slug' => str($name)->slug(),
            'name' => str($name)->title(),
            'description' => fake()->sentence(),
        ];
    }
}
