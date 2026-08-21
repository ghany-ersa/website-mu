<?php

namespace Database\Factories;

use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'organization_type_id' => OrganizationType::factory(),
            'name' => str($name)->title(),
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
            'thumbnail_path' => null,
            'structure' => [
                'pages' => [
                    ['slug' => 'home', 'name' => 'Home', 'sections' => ['hero', 'tentang', 'berita', 'kontak']],
                ],
            ],
            'is_active' => true,
        ];
    }
}
