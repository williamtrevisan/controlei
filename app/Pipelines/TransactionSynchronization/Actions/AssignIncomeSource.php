<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\GetAllUserIncomeSource;
use App\DataTransferObjects\SynchronizationData;
use App\DataTransferObjects\TransactionData;
use App\Models\IncomeSource;
use Closure;

readonly class AssignIncomeSource
{
    public function __construct(
        private GetAllUserIncomeSource $getAllUserIncomeSource,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $incomeSources = $this->getAllUserIncomeSource->execute();

        $transactions = $data->transactions
            ->map(function (TransactionData $transaction) use ($incomeSources): TransactionData {
                $incomeSource = $incomeSources
                    ->where('matcher_regex')
                    ->first(fn (IncomeSource $incomeSource): bool => str($transaction->description)->isMatch($incomeSource->matcher_regex));

                return is_null($incomeSource)
                    ? $transaction
                    : $transaction->withIncomeSourceId($incomeSource->id);
                });

        return $next($data->withTransactions($transactions));
    }
}

