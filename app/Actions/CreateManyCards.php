<?php

namespace App\Actions;

use App\DataTransferObjects\CardData;
use App\Repositories\Contracts\CardRepository;
use Illuminate\Support\Collection;

class CreateManyCards
{
    public function __construct(
        private readonly CardRepository $cardRepository,
    ) {
    }

    /**
     * @param Collection<int, CardData> $cards
     * @return bool
     */
    public function execute(Collection $cards): bool
    {
        return $this->cardRepository->createMany($cards);
    }
}
