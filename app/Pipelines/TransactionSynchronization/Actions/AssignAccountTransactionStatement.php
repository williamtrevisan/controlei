<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\GetAllUserStatements;
use App\DataTransferObjects\SynchronizationData;
use App\Models\Statement;
use Banklink\Entities\Transaction;
use Closure;

readonly class AssignAccountTransactionStatement
{
    public function __construct(
        private GetAllUserStatements $getAllUserStatements,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $statements = $this->getAllUserStatements->execute();

        $statementMap = $data->accountTransactions
            ->mapWithKeys(function (Transaction $transaction) use ($statements, $data): array {
                $statement = $statements
                    ->first(function (Statement $statement) use ($transaction): bool {
                        return $statement->period->value() === $statement->period($transaction->date())->value();
                    });

                return $statement
                    ? [$this->hash($transaction, $data->account->id) => $statement->id]
                    : [];
            });

        return $next($data->withStatementMap($statementMap));
    }

    private function hash(Transaction $transaction, string $accountId): string
    {
        return hash('sha256', implode('|', [
            $accountId,
            null, // cardId
            $transaction->date()->format('Y-m-d'),
            $transaction->description(),
            $transaction->amount()->getMinorAmount()->toInt(),
            $transaction->installments()?->current(),
        ]));
    }
}

