<?php

namespace App\Actions;

use App\Repositories\Contracts\CardRepository;
use Illuminate\Support\Collection;

class GetAllCards
{
    public function __construct(
        private readonly CardRepository $cardRepository
    ) {}

    public function execute(): Collection
    {
        return $this->cardRepository->getAllUserCards();
    }
}
