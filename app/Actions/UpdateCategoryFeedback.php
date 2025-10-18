<?php

namespace App\Actions;

use App\Models\TransactionCategoryFeedback;
use App\Repositories\Contracts\TransactionCategoryFeedbackRepository;

readonly class UpdateCategoryFeedback
{
    public function __construct(
        private TransactionCategoryFeedbackRepository $transactionCategoryFeedbackRepository,
    ) {
    }

    public function execute(TransactionCategoryFeedback $feedback, int $categoryId): bool
    {
        $transaction = $feedback->transaction;

        return $this->transactionCategoryFeedbackRepository
            ->update($feedback, [
                'description' => $transaction->description,
                'direction' => $transaction->direction->value,
                'amount' => $transaction->amount->getMinorAmount()->toInt(),
                'kind' => $transaction->kind->value,
                'payment_method' => $transaction->payment_method->value,
                'total_installments' => $transaction->total_installments > 1 ? $transaction->total_installments : null,
                'category_id' => $categoryId,
            ]);
    }
}

