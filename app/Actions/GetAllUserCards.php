<?php

namespace App\Actions;

use App\Repositories\Contracts\CardRepository;
use Illuminate\Support\Collection;

class GetAllUserCards
{
    public function __construct(
        private readonly CardRepository $cardRepository
    ) {}

    public function execute(): Collection
    {
        return $this->cardRepository->getAllUserCards();
    }
}
