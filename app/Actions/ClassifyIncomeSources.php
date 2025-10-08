<?php

namespace App\Actions;

use App\Models\IncomeSource;
use App\Models\Transaction;

class ClassifyIncomeSources
{
    public function __construct(
        private readonly GetAllUserIncomeTransactions $getAllUserIncomeTransactions,
        private readonly GetAllUserIncomeSource $getAllUserIncomeSource,
        private readonly UpdateTransactionIncomeSource $updateTransactionIncomeSource,
    ) {
    }

    public function execute(): void
    {
        $incomeSources = $this->getAllUserIncomeSource->execute();

        $this->getAllUserIncomeTransactions
            ->execute()
            ->each(function (Transaction $transaction) use ($incomeSources): void {
                $incomeSource = $incomeSources
                    ->first(fn (IncomeSource $incomeSource): bool => str($transaction->description)->isMatch($incomeSource->matcher_regex));
                if (! $incomeSource) {
                    return;
                }

                $this->updateTransactionIncomeSource->execute($transaction, $incomeSource);
            });
    }
}
