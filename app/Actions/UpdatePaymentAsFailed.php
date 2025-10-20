<?php

namespace App\Actions;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepository;

readonly class UpdatePaymentAsFailed
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    public function execute(Payment $payment): Payment
    {
        return $this->paymentRepository->update($payment, [
            'status' => PaymentStatus::Failed,
        ]);
    }
}

