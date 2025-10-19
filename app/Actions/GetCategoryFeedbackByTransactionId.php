<?php

namespace App\Actions;

use App\Models\TransactionCategoryFeedback;
use App\Repositories\Contracts\TransactionCategoryFeedbackRepository;

readonly class GetCategoryFeedbackByTransactionId
{
    public function __construct(
        private TransactionCategoryFeedbackRepository $transactionCategoryFeedbackRepository,
    ) {
    }

    public function execute(string $transactionId): ?TransactionCategoryFeedback
    {
        return $this->transactionCategoryFeedbackRepository
            ->findByTransactionId($transactionId);
    }
}

