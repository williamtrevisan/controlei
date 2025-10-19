<?php

namespace App\Repositories;

use App\Models\TransactionCategoryFeedback;
use App\Repositories\Contracts\TransactionCategoryFeedbackRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TransactionCategoryFeedbackEloquentRepository implements TransactionCategoryFeedbackRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {
    }

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    public function findByTransactionId(string $transactionId): ?TransactionCategoryFeedback
    {
        return $this->builder()
            ->where('transaction_id', $transactionId)
            ->first();
    }

    public function create(array $attributes): TransactionCategoryFeedback
    {
        return $this->builder()
            ->create($attributes);
    }

    public function update(TransactionCategoryFeedback $feedback, array $attributes): bool
    {
        return $feedback->update($attributes);
    }

    public function all(): Collection
    {
        return $this->builder()
            ->get();
    }
}

