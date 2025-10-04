<?php

namespace Database\Factories;

use App\Enums\IncomeFrequency;
use App\Enums\IncomeSourceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IncomeSource>
 */
class IncomeSourceFactory extends Factory
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
            'name' => $name = fake()->word(),
            'slug' => str($name)->slug(),
            'type' => fake()->randomElement(IncomeSourceType::cases()),
            'frequency' => fake()->randomElement(IncomeFrequency::cases()),
            'matcher_regex' => '',
            'average_amount' => fake()->numberBetween(300000, 1000000),
            'active' => true,
        ];
    }
}
