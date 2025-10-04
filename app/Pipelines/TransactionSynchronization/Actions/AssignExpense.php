<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\GetAllUserExpenses;
use App\DataTransferObjects\SynchronizationData;
use App\DataTransferObjects\TransactionData;
use Closure;

readonly class AssignExpense
{
    public function __construct(
        private GetAllUserExpenses $getAllUserExpenses,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $expenses = $this->getAllUserExpenses->execute();

        $transactions = $data->transactions
            ->map(function (TransactionData $transaction) use ($expenses): TransactionData {
                $expense = $expenses
                    ->where('matcher_regex')
                    ->first(fn ($expense): bool => str($transaction->description)->isMatch($expense->matcher_regex));

                return is_null($expense)
                    ? $transaction
                    : $transaction->withExpenseId($expense->id);
                });

        return $next($data->withTransactions($transactions));
    }
}

