<?php

namespace App\Observers;

use App\Actions\CreateFutureTransactions;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepository;

class TransactionObserver
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly CreateFutureTransactions $createFutureTransactions,
    ) {
    }

    public function creating(Transaction $transaction): void
    {
        if ($this->transactionRepository->existsBy('hash', ($hash = $transaction->hash()))) {
            return;
        }

        $transaction->hash = $hash;
    }
}
