<?php

namespace App\Actions;

use App\DataTransferObjects\TransactionData;
use App\Repositories\Contracts\TransactionRepository;
use Illuminate\Support\LazyCollection;

class CreateManyTransactions
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function execute(LazyCollection $transactions): int
    {
       $uniqueTransactions = $transactions
            ->unique('hash')
            ->reject(fn (TransactionData $transaction) => $this->transactionRepository->existsBy('hash', $transaction->hash));

       $this->transactionRepository->createMany($uniqueTransactions);

       return $uniqueTransactions->count();
    }
}
