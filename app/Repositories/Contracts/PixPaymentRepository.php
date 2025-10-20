<?php

namespace App\Repositories\Contracts;

use App\Models\PixPayment;

interface PixPaymentRepository
{
    public function create(array $data): PixPayment;

    public function update(PixPayment $pixPayment, array $data): PixPayment;
}

