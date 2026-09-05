<?php

namespace Database\Factories;

use App\Models\DonationProgram;
use App\Models\DonationTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonationTransaction>
 */
class DonationTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'donation_program_id' => DonationProgram::factory(),
            'donor_name' => fake()->name(),
            'amount' => fake()->numberBetween(20_000, 2_000_000),
            'donated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
