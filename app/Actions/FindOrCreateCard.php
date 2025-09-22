<?php

namespace App\Actions;

use App\DataTransferObjects\CardData;
use App\Models\Card;
use App\Repositories\Contracts\CardRepository;
use Banklink\Entities\Holder;
use Illuminate\Support\Collection;

class FindOrCreateCard
{
    public function __construct(
        private readonly CardRepository $cardRepository
    ) {
    }

    /**
     * @param int $accountId
     * @param Holder $holder
     * @param Collection<int, \Banklink\Entities\Card> $cards
     * @return Card
     */
    public function execute(int $accountId, Holder $holder, Collection $cards): Card
    {
        $lastFourDigits = $holder->lastFourDigits();

        $bankCard = $cards
            ->first(fn (\Banklink\Entities\Card $card) => $card->lastFourDigits() === $lastFourDigits);
        if (! $bankCard) {
            return $this->cardRepository
                ->findOrCreate(CardData::fromHolder($holder, $accountId));
        }

        return $this->cardRepository
            ->findOrCreate(CardData::fromBankCard($bankCard, $accountId));
    }
}
