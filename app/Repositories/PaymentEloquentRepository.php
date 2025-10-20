<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentEloquentRepository implements PaymentRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {}

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    public function create(array $data): Payment
    {
        return $this->builder()
            ->create($data);
    }

    public function update(Payment $payment, array $data): Payment
    {
        return tap($payment)
            ->update($data);
    }
}

