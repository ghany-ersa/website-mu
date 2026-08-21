<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
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
            'type' => 'program',
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'icon' => null,
            'order' => 0,
        ];
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'layanan']);
    }
}
