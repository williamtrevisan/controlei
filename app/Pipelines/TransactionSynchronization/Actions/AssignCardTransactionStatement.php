<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\GetAllUserCards;
use App\Actions\GetAllUserStatements;
use App\DataTransferObjects\SynchronizationData;
use App\Models\Card;
use App\Models\Statement;
use Banklink\Entities\Transaction;
use Closure;

readonly class AssignCardTransactionStatement
{
    public function __construct(
        private GetAllUserCards $getAllUserCards,
        private GetAllUserStatements $getAllUserStatements,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $cards = $this->getAllUserCards->execute();
        $statements = $this->getAllUserStatements->execute();

        $statementMap = $data->cardTransactions
            ->mapWithKeys(function (Transaction $transaction) use ($statements, $data, $cards): array {
                $card = $cards->firstWhere('last_four_digits', $transaction->holder()->lastFourDigits());
                if (! $card) {
                    return [];
                }

                $statement = $statements
                    ->first(function (Statement $statement) use ($transaction): bool {
                        return $statement->period->value() === $transaction->statement()->period()->value();
                    });

                return $statement
                    ? [$this->hash($transaction, $card) => $statement->id]
                    : [];
            });

        return $next($data->withStatementMap(($data->statementMap ?? collect()->lazy())->merge($statementMap)));
    }

    private function hash(Transaction $transaction, Card $card): string
    {
        return hash('sha256', implode('|', [
            $card->account->id,
            $card->id,
            $transaction->date()->format('Y-m-d'),
            $transaction->description(),
            $transaction->amount()->getMinorAmount()->toInt(),
            $transaction->installments()?->current(),
        ]));
    }
}

