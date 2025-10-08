<?php

namespace App\Actions;

use App\Models\IncomeSource;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepository;

class UpdateTransactionIncomeSource
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function execute(Transaction $transaction, IncomeSource $incomeSource): bool
    {
        return $this->transactionRepository->update($transaction, [
            'income_source_id' => $incomeSource->id,
        ]);
    }
}

