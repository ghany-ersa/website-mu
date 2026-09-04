<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
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
            'author_id' => null,
            'title' => $title,
            'slug' => str($title)->slug().'-'.fake()->unique()->numberBetween(1, 100000),
            'category' => fake()->randomElement(['Produk', 'Tips Digitalisasi', 'Kabar Muhammadiyah', 'Studi Kasus']),
            'cover_image' => null,
            'body' => fake()->paragraphs(5, true),
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
