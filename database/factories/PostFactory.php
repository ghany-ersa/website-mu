<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Organization;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'organization_id' => Organization::factory(),
            'author_id' => null,
            'title' => $title,
            'slug' => str($title)->slug().'-'.fake()->unique()->numberBetween(1, 100000),
            'category' => fake()->randomElement(['Kegiatan', 'Pengumuman', 'Prestasi']),
            'image' => null,
            'body' => fake()->paragraphs(3, true),
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublishStatus::Published,
            'published_at' => now(),
        ]);
    }
}
