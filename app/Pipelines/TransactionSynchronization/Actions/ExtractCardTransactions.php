<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\DataTransferObjects\SynchronizationData;
use Banklink\Entities\Card as BankCard;
use Banklink\Entities\CardStatement;
use Banklink\Entities\Holder;
use Closure;

readonly class ExtractCardTransactions
{
    public function handle(SynchronizationData $data, Closure $next): mixed
    {
        $transactions = $data->bank->account()
            ->cards()->all()
            ->flatMap(fn (BankCard $card) => $card->statements()->all())
            ->flatMap(fn (CardStatement $statement) => $statement->holders())
            ->flatMap(fn (Holder $holder) => $holder->transactions())
            ->lazy();

        return $next($data->withCardTransactions($transactions));
    }
}
