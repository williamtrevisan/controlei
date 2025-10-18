<?php

namespace Database\Factories;

use App\Enums\TransactionDirection;
use App\Enums\TransactionKind;
use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'card_id' => null,
            'income_source_id' => null,
            'expense_id' => null,
            'category_id' => null,
            'statement_id' => null,
            'parent_transaction_id' => null,
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'description' => fake()->words(3, true),
            'amount' => fake()->numberBetween(1000, 100000),
            'direction' => fake()->randomElement(TransactionDirection::cases()),
            'kind' => fake()->randomElement(TransactionKind::cases()),
            'payment_method' => fake()->randomElement(TransactionPaymentMethod::cases()),
            'current_installment' => null,
            'total_installments' => null,
            'status' => TransactionStatus::Scheduled,
            'matcher_regex' => null,
            'hash' => fake()->sha256(),
        ];
    }

    public function inflow(): self
    {
        return $this
            ->state([
                'direction' => TransactionDirection::Inflow,
            ]);
    }

    public function outflow(): self
    {
        return $this
            ->state([
                'direction' => TransactionDirection::Outflow,
            ]);
    }

    public function expense(): self
    {
        return $this
            ->state([
                'direction' => TransactionDirection::Outflow,
                'kind' => TransactionKind::Purchase,
            ]);
    }
}
