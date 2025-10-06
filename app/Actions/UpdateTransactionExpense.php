<?php

namespace App\Actions;

use App\Models\Expense;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepository;

class UpdateTransactionExpense
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function execute(Transaction $transaction, Expense $expense): bool
    {
        return $this->transactionRepository->update($transaction, [
            'expense_id' => $expense->id,
        ]);
    }
}

