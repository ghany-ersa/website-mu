<?php

namespace Database\Factories;

use App\Models\GalleryPhoto;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryPhoto>
 */
class GalleryPhotoFactory extends Factory
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
            'url' => fake()->imageUrl(),
            'caption' => null,
            'order' => 0,
        ];
    }
}
