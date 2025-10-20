<?php

namespace Database\Factories;

use App\Enums\RevenuePeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RevenueSnapshot>
 */
class RevenueSnapshotFactory extends Factory
{
    public function definition(): array
    {
        $totalCustomers = fake()->numberBetween(100, 1000);
        $payingCustomers = fake()->numberBetween(10, $totalCustomers);
        $mrr = $payingCustomers * fake()->numberBetween(1000, 5000);

        return [
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'period' => fake()->randomElement(RevenuePeriod::cases()),
            'monthly_recurring_revenue' => $mrr,
            'total_revenue' => $mrr + fake()->numberBetween(0, 10000),
            'total_customers' => $totalCustomers,
            'paying_customers' => $payingCustomers,
            'new_customers' => fake()->numberBetween(0, 50),
            'churned_customers' => fake()->numberBetween(0, 20),
            'successful_payments' => fake()->numberBetween(0, $payingCustomers),
            'failed_payments' => fake()->numberBetween(0, 10),
            'monthly_recurring_revenue_growth_rate' => fake()->optional()->randomFloat(2, -10, 50),
            'churn_rate' => fake()->optional()->randomFloat(2, 0, 10),
        ];
    }
}

