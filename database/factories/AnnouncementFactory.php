<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Announcement;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
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
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(),
            'priority' => fake()->randomElement(['Rendah', 'Sedang', 'Tinggi']),
            'valid_until' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => PublishStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => ['status' => PublishStatus::Published]);
    }
}
