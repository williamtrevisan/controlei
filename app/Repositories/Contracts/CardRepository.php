<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\CardData;
use App\Models\Card;
use Illuminate\Support\Collection;

interface CardRepository
{
    public function findOrCreate(CardData $card): Card;

    /**
     * @return Collection<int, Card>
     */
    public function getAllMatcherRegex(): Collection;

    /**
     * @return Collection<int, Card>
     */
    public function getAllUserCards(): Collection;
}
