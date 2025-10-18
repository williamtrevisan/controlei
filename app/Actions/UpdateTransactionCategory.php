<?php

namespace App\Actions;

use App\Models\Transaction;
use App\Models\TransactionCategoryFeedback;
use App\Repositories\Contracts\TransactionRepository;
use Illuminate\Support\Facades\DB;

class UpdateTransactionCategory
{
    public function __construct(
        private GetCategoryFeedbackByTransactionId $getCategoryFeedbackByTransactionId,
        private CreateCategoryFeedback $createCategoryFeedback,
        private UpdateCategoryFeedback $updateCategoryFeedback,
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function execute(Transaction $transaction, int $categoryId): bool
    {
        return DB::transaction(function () use ($transaction, $categoryId): bool {
            $feedback = $this->getCategoryFeedbackByTransactionId->execute($transaction->id);
            $this->upsertFeedback($feedback, $transaction, $categoryId);

            return $this->transactionRepository->updateBy($transaction, [
                'category_id' => $categoryId,
            ]);
        });
    }

    private function upsertFeedback(?TransactionCategoryFeedback $feedback, Transaction $transaction, int $categoryId): void
    {
        if (! $feedback) {
            $this->createCategoryFeedback->execute($transaction, $categoryId);

            return;
        }

        $this->updateCategoryFeedback->execute($feedback, $categoryId);
    }
}

