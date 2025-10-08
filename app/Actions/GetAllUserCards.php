<?php

namespace App\Actions;

use App\Repositories\Contracts\CardRepository;
use Illuminate\Support\Collection;

readonly class GetAllUserCards
{
    public function __construct(
        private CardRepository $cardRepository
    ) {
    }

    public function execute(): Collection
    {
        return $this->cardRepository->getAllUserCards();
    }
}
