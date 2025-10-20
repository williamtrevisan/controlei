<?php

namespace App\Repositories;

use App\Models\PixPayment;
use App\Repositories\Contracts\PixPaymentRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PixPaymentEloquentRepository implements PixPaymentRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {}

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    public function create(array $data): PixPayment
    {
        return $this->builder()
            ->create($data);
    }

    public function update(PixPayment $pixPayment, array $data): PixPayment
    {
        return tap($pixPayment)
            ->update($data);
    }
}

