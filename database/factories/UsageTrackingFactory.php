<?php

namespace Database\Factories;

use App\Enums\ResourceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UsageTracking>
 */
class UsageTrackingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'resource_type' => fake()->randomElement(ResourceType::cases()),
            'year' => fake()->year(),
            'month' => fake()->numberBetween(1, 12),
            'count' => fake()->numberBetween(0, 100),
        ];
    }
}

