<?php

namespace Database\Factories;

use App\Models\DonationProgram;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonationProgram>
 */
class DonationProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => str($name)->slug().'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->sentence(),
            'target_amount' => fake()->numberBetween(5_000_000, 200_000_000),
            'cover_photo' => fake()->imageUrl(),
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonths(3),
        ];
    }
}
