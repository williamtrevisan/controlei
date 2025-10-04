<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\CreateManyCards;
use App\Actions\FindManyCards;
use App\DataTransferObjects\CardData;
use App\DataTransferObjects\SynchronizationData;
use App\Models\Card;
use Banklink\Entities\Card as BankCard;
use Banklink\Entities\CardStatement;
use Banklink\Entities\Holder;
use Closure;
use Illuminate\Support\Collection;

final readonly class FindOrCreateManyCards
{
    public function __construct(
        private FindManyCards $findManyCards,
        private CreateManyCards $createManyCards,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        ($bankCards = $data->bank->account()
            ->cards()->all())
            ->flatMap(fn (BankCard $card) => $card->statements()->all())
            ->flatMap(fn (CardStatement $statement) => $statement->holders())
            ->tap(function (Collection $holders) use ($bankCards, $data): void {
                $cards = $holders
                    ->unique(fn (Holder $holder): string => $holder->lastFourDigits())
                    ->map(fn (Holder $holder): CardData => CardData::from($holder, $data->account, cards: $bankCards));

                $existentCards = $this->findManyCards->execute($cards, $data->account);

                $cards
                    ->reject(fn (CardData $card): bool => $existentCards->contains(fn (Card $existent) => $existent->last_four_digits === $card->lastFourDigits))
                    ->pipe(fn (Collection $cards): bool => $this->createManyCards->execute($cards));
            });

        return $next($data);
    }
}
