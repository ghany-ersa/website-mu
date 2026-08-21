<?php

namespace Database\Factories;

use App\Models\Officer;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Officer>
 */
class OfficerFactory extends Factory
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
            'name' => fake()->name(),
            'role' => fake()->randomElement(['Ketua', 'Sekretaris', 'Bendahara', 'Anggota']),
            'photo' => null,
            'order' => 0,
        ];
    }
}
