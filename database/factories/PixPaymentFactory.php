<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PixPayment>
 */
class PixPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gateway' => 'woovi',
            'charge_id' => fake()->uuid(),
            'transaction_id' => fake()->optional()->uuid(),
            'qrcode_text' => fake()->regexify('[A-Z0-9]{100}'),
            'qrcode_image' => fake()->optional()->imageUrl(),
            'payer_name' => fake()->optional()->name(),
            'payer_document' => fake()->optional()->numerify('###########'),
        ];
    }
}

