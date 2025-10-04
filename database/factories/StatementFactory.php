<?php

namespace Database\Factories;

use App\Enums\StatementStatus;
use App\Models\Account;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Statement>
 */
class StatementFactory extends Factory
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
            'card_id' => Card::factory(),
            'parent_statement_id' => null,
            'period' => now()->format('Y-m'),
            'closing_date' => now()->addDays(25),
            'due_date' => now()->addDays(32),
            'status' => StatementStatus::Open,
            'amount' => 0,
        ];
    }
}


