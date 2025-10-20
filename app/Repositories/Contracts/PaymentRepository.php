<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;

interface PaymentRepository
{
    public function create(array $data): Payment;

    public function update(Payment $payment, array $data): Payment;
}

