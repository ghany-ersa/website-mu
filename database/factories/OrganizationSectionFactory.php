<?php

namespace Database\Factories;

use App\Models\OrganizationPage;
use App\Models\OrganizationSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationSection>
 */
class OrganizationSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_page_id' => OrganizationPage::factory(),
            'key' => 'hero',
            'variant' => null,
            'content' => [
                'headline' => fake()->sentence(),
                'subheadline' => fake()->sentence(),
            ],
            'order' => 0,
            'is_visible' => true,
        ];
    }
}
