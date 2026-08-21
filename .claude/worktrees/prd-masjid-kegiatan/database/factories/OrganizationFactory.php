<?php

namespace Database\Factories;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'user_id' => User::factory(),
            'organization_type_id' => OrganizationType::factory(),
            'template_id' => null,
            'name' => $name,
            'slug' => str($name)->slug(),
            'region' => fake()->city(),
            'description' => fake()->sentence(),
            'status' => OrganizationStatus::Draft,
            'published_at' => null,
        ];
    }

    /**
     * Indicate that the organization has been published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrganizationStatus::Published,
            'published_at' => now(),
        ]);
    }
}
