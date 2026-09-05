<?php

namespace Database\Factories;

use App\Models\FinancialReport;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialReport>
 */
class FinancialReportFactory extends Factory
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
            'period_month' => now()->month,
            'period_year' => now()->year,
            'transacted_at' => now(),
            'type' => fake()->randomElement(['income', 'expense']),
            'category' => fake()->word(),
            'amount' => fake()->numberBetween(50_000, 5_000_000),
        ];
    }
}
