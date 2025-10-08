<?php

namespace Database\Factories;

use App\Enums\CardBrand;
use App\Enums\CardType;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'last_four_digits' => fake()->numerify('####'),
            'type' => fake()->randomElement(CardType::cases()),
            'brand' => fake()->randomElement(CardBrand::cases()),
            'limit' => fake()->numberBetween(100000, 1000000),
            'due_day' => fake()->numberBetween(1, 28),
            'matcher_regex' => null,
        ];
    }
}
