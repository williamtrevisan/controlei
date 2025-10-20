<?php

namespace Database\Factories;

use App\Enums\AnalyticsEventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Analytics>
 */
class AnalyticsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'analyticsable_type' => fake()->randomElement([
                \App\Models\Subscription::class,
                \App\Models\Payment::class,
            ]),
            'analyticsable_id' => fake()->numberBetween(1, 100),
            'event_type' => fake()->randomElement(AnalyticsEventType::cases()),
            'amount' => fake()->optional()->numberBetween(500, 5000),
            'data' => fake()->optional()->passthrough([
                'ip' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ]),
            'event_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

