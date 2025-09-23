<?php

namespace App\Actions;

use App\Models\Card;
use App\Repositories\Contracts\CardRepository;
use Illuminate\Support\Collection;

class GetAllSharedCards
{
    public function __construct(
        private readonly CardRepository $cardRepository
    ) {}

    public function execute(): Collection
    {
        return $this->cardRepository->findSharedCards();
    }
}
