<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->slug(),
            'price' => fake()->numberBetween(0, 5000),
            'max_accounts' => fake()->numberBetween(1, 5),
            'max_synchronizations_per_month' => fake()->numberBetween(1, 10),
            'max_imports_per_month' => fake()->optional()->numberBetween(5, 50),
            'history_days' => fake()->optional()->randomElement([30, 60, 90, 180]),
            'auto_classification' => fake()->boolean(),
            'expense_tracking' => fake()->boolean(),
            'expense_projections' => fake()->boolean(),
            'active' => true,
        ];
    }
}

