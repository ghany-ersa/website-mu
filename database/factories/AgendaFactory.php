<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Agenda;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agenda>
 */
class AgendaFactory extends Factory
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
            'starts_at' => fake()->dateTimeBetween('now', '+2 months'),
            'location' => fake()->city(),
            'contact_person' => fake()->name(),
            'description' => fake()->sentence(),
            'registration_url' => null,
            'status' => PublishStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => ['status' => PublishStatus::Published]);
    }
}
