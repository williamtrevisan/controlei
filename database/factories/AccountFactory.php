<?php

namespace Database\Factories;

use App\Enums\AccountBank;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'user_id' => User::factory(),
            'type' => AccountType::Checking,
            'bank' => AccountBank::Itau,
            'agency' => fake()->numerify('####'),
            'account' => fake()->numerify('#####'),
            'account_digit' => fake()->numerify('#'),
        ];
    }
}
