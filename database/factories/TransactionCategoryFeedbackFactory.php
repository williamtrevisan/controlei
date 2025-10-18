<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionCategoryFeedback>
 */
class TransactionCategoryFeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'description' => fake()->words(3, true),
            'direction' => fake()->randomElement(['inflow', 'outflow']),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'kind' => fake()->randomElement(['normal', 'transfer', 'fee', 'cashback', 'invoice_payment']),
            'payment_method' => fake()->randomElement(['debit', 'credit', 'pix', 'ted', 'doc', 'boleto']),
            'total_installments' => fake()->optional(0.3)->numberBetween(2, 12),
            'category_id' => Category::factory(),
        ];
    }
}

