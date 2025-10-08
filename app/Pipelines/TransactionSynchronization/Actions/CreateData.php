<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\GetAllUserCards;
use App\DataTransferObjects\SynchronizationData;
use App\DataTransferObjects\TransactionData;
use Banklink\Entities\Transaction;
use Closure;

readonly class CreateData
{
    public function __construct(
        private GetAllUserCards $getAllUserCards,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $cards = $this->getAllUserCards->execute();

        $accountTransactions = ($data->accountTransactions ?? collect()->lazy())
            ->map(function (Transaction $transaction) use ($data) {
                return TransactionData::from(
                    $transaction,
                    accountId: $data->account->id,
                    statementId: $data->statementMap?->get($this->hash($transaction, $data->account->id)),
                );
            });

        $cardTransactions = ($data->cardTransactions ?? collect()->lazy())
            ->map(function (Transaction $transaction) use ($data, $cards) {
                $card = $cards->firstWhere('last_four_digits', $transaction->holder()->lastFourDigits());

                return TransactionData::from(
                    $transaction,
                    accountId: $data->account->id,
                    cardId: $card->id,
                    statementId: $data->statementMap?->get($this->hash($transaction, $data->account->id, $card->id)),
                );
            });

        return $next($data->withTransactions($accountTransactions->concat($cardTransactions)));
    }

    private function hash(Transaction $transaction, string $accountId, ?string $cardId = null): string
    {
        return hash('sha256', implode('|', [
            $accountId,
            $cardId,
            $transaction->date()->format('Y-m-d'),
            $transaction->description(),
            $transaction->amount()->getMinorAmount()->toInt(),
            $transaction->installments()?->current(),
        ]));
    }
}

