<?php

namespace App\Actions;

use App\Models\Expense;
use App\Models\Transaction;

class ClassifyExpenses
{
    public function __construct(
        private readonly GetAllUserExpenseTransactions $getAllUserExpenseTransactions,
        private readonly GetAllUserExpenses $getAllUserExpenses,
        private readonly UpdateTransactionExpense $updateTransactionExpense,
    ) {
    }

    public function execute(): void
    {
        $expenses = $this->getAllUserExpenses->execute();

        $this->getAllUserExpenseTransactions
            ->execute()
            ->each(function (Transaction $transaction) use ($expenses): void {
                $expense = $expenses
                    ->where('matcher_regex')
                    ->first(fn (Expense $expense): bool => str($transaction->description)->isMatch($expense->matcher_regex));
                if (! $expense) {
                    return;
                }

                $this->updateTransactionExpense->execute($transaction, $expense);
            });
    }
}

