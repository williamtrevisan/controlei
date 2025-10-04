<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\GetAllUserStatements;
use App\DataTransferObjects\SynchronizationData;
use App\DataTransferObjects\TransactionData;
use App\Models\Statement;
use Closure;

readonly class AssignStatement
{
    public function __construct(
        private GetAllUserStatements $getAllUserStatements,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $statements = $this->getAllUserStatements->execute();

        $transactions = $data->transactions
            ->map(function (TransactionData $transaction) use ($statements): TransactionData {
                $statement = $statements
                    ->first(function (Statement $statement) use ($transaction): bool {
                        $period = $transaction->cardId
                            ? $statement->period()
                            : $statement->period($transaction->date);

                        return $statement->period === $period->value();
                    });

                return is_null($statement)
                    ? $transaction
                    : $transaction->withStatementId($statement->id);
                });

        return $next($data->withTransactions($transactions));
    }
}

