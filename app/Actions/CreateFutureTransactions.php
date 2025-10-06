<?php

namespace App\Actions;

use App\DataTransferObjects\TransactionData;
use App\Models\Statement;
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
                $periodOffset = $installment - 1;

                $period = $transaction->statement
                    ->period
                    ->advance($periodOffset)
                    ->value();

                return TransactionData::fromEntity(
                    $transaction,
                    $installment,
                    Statement::query()->where('period', $period)->first()
                );
            })
            ->pipe(fn (LazyCollection $transactions) => $this->createManyTransactions->execute($transactions));
    }
}
