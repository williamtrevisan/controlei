<?php

namespace App\Actions;

use App\DataTransferObjects\TransactionData;
use App\Models\Transaction;
use Illuminate\Support\LazyCollection;

class CreateFutureTransactions
{
    public function __construct(
        private readonly CreateManyTransactions $createManyTransactions,
    ) {
    }

    public function execute(Transaction $transaction): void
    {
        if (is_null($transaction->current_installment)) {
            return;
        }

        collect()
            ->lazy()
            ->range($transaction->current_installment + 1, $transaction->total_installments)
            ->map(function (int $installment) use ($transaction): TransactionData {
                return TransactionData::fromEntity(
                    $transaction,
                    $installment,
                    $transaction->statement_period->advance($transaction->current_installment++)
                );
            })
            ->pipe(fn (LazyCollection $transactions) => $this->createManyTransactions->execute($transactions));
    }
}
