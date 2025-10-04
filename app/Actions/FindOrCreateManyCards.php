<?php

namespace App\Actions;

use App\DataTransferObjects\CardData;
use App\Models\Card;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class FindOrCreateManyCards
{
    public function execute(Collection $cardsData): LazyCollection
    {
        return $cardsData
            ->map(fn (CardData $cardData) => $this->findOrCreateCard($cardData))
            ->lazy();
    }

    private function findOrCreateCard(CardData $cardData): Card
    {
        return Card::query()
            ->where('account_id', $cardData->accountId)
            ->where('last_four_digits', $cardData->lastFourDigits)
            ->where('holder_name', $cardData->holderName)
            ->firstOrCreate(
                [
                    'account_id' => $cardData->accountId,
                    'last_four_digits' => $cardData->lastFourDigits,
                    'holder_name' => $cardData->holderName,
                ],
                $cardData->toArray()
            );
    }
}
