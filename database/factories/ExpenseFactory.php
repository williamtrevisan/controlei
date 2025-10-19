<?php

namespace Database\Factories;

use App\Enums\ExpenseFrequency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => fake()->optional(0.8)->numberBetween(1, 15),
            'description' => fake()->word(),
            'frequency' => fake()->randomElement(ExpenseFrequency::cases()),
            'matcher_regex' => '',
            'average_amount' => fake()->numberBetween(5000, 50000),
            'active' => true,
        ];
    }
}

