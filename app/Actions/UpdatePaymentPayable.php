<?php

namespace App\Actions;

use App\Models\Payment;
use App\Models\PixPayment;
use App\Repositories\Contracts\PaymentRepository;

readonly class UpdatePaymentPayable
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    public function execute(Payment $payment, PixPayment $pixPayment): Payment
    {
        return $this->paymentRepository->update($payment, [
            'payable_id' => $pixPayment->id,
        ]);
    }
}

