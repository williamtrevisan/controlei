<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\PixPayment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $pixPayment = PixPayment::factory()->create();

        return [
            'subscription_id' => Subscription::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(500, 5000),
            'status' => PaymentStatus::Pending,
            'payment_method' => PaymentMethod::Pix,
            'payable_type' => PixPayment::class,
            'payable_id' => $pixPayment->id,
            'paid_at' => null,
            'expires_at' => now()->addHours(24),
        ];
    }
}

