<?php

namespace App\Repositories\Contracts;

use App\Models\TransactionCategoryFeedback;
use Illuminate\Support\Collection;

interface TransactionCategoryFeedbackRepository
{
    public function findByTransactionId(string $transactionId): ?TransactionCategoryFeedback;

    public function create(array $attributes): TransactionCategoryFeedback;

    public function update(TransactionCategoryFeedback $feedback, array $attributes): bool;

    public function all(): Collection;
}

