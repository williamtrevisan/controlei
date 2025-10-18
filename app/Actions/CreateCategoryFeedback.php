<?php

namespace App\Actions;

use App\Models\Transaction;
use App\Models\TransactionCategoryFeedback;
use App\Repositories\Contracts\TransactionCategoryFeedbackRepository;

readonly class CreateCategoryFeedback
{
    public function __construct(
        private TransactionCategoryFeedbackRepository $transactionCategoryFeedbackRepository,
    ) {
    }

    public function execute(Transaction $transaction, int $categoryId): TransactionCategoryFeedback
    {
        return $this->transactionCategoryFeedbackRepository
            ->create([
                'transaction_id' => $transaction->id,
                'description' => $transaction->description,
                'direction' => $transaction->direction->value,
                'amount' => $transaction->amount->getAmount()->toFloat(),
                'kind' => $transaction->kind->value,
                'payment_method' => $transaction->payment_method->value,
                'total_installments' => $transaction->total_installments > 1 ? $transaction->total_installments : null,
                'category_id' => $categoryId,
            ]);
    }
}

