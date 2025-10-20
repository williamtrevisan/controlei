<?php

namespace Database\Factories;

use App\Enums\SubscriptionEventType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionEvent>
 */
class SubscriptionEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_id' => Subscription::factory(),
            'event_type' => fake()->randomElement(SubscriptionEventType::cases()),
            'from_id' => fake()->optional()->randomElement([null, Plan::factory()]),
            'to_id' => fake()->optional()->randomElement([null, Plan::factory()]),
            'monthly_recurring_revenue_change' => fake()->numberBetween(-5000, 5000),
        ];
    }
}

