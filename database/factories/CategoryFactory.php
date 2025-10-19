<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $icons = [
            'shopping-cart', 'truck', 'heart', 'home', 'academic-cap',
            'film', 'sparkles', 'user-circle', 'device-phone-mobile',
            'banknotes', 'gift', 'arrow-trending-up', 'chart-bar', 'ellipsis-horizontal-circle',
        ];

        $colors = [
            'purple', 'pink', 'amber', 'green', 'blue', 'cyan',
            'red', 'orange', 'yellow', 'indigo', 'violet', 'emerald', 'rose', 'slate', 'gray',
        ];

        return [
            'description' => fake()->words(2, true),
            'icon' => fake()->randomElement($icons),
            'color' => fake()->randomElement($colors),
            'active' => true,
        ];
    }
}
