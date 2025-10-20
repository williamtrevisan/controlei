<?php

namespace App\Actions;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PixPayment;
use App\Models\Subscription;
use App\Models\User;
use App\Repositories\Contracts\PaymentRepository;

readonly class CreatePayment
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    public function execute(int $amount, User $user, Subscription $subscription): Payment
    {
        return $this->paymentRepository->create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => PaymentStatus::Pending,
            'payment_method' => PaymentMethod::Pix,
            'payable_type' => PixPayment::class,
            'payable_id' => null,
            'expires_at' => now()->addDay(),
        ]);
    }
}

