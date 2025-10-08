<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\CardData;
use App\DataTransferObjects\TransactionData;
use App\Models\Card;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

interface CardRepository
{
    public function findOrCreate(CardData $card): Card;

    /**
     * @param Collection<int, CardData> $values
     * @return bool
     */
    public function createMany(Collection $values): bool;

    /**
     * @param string|Closure $column
     * @param mixed $value
     * @return Collection<int, Card>
     */
    public function findManyBy(string|\Closure $column, mixed $value = null): Collection;

    /**
     * @return Collection<int, Card>
     */
    public function getAllMatcherRegex(): Collection;

    /**
     * @return Collection<int, Card>
     */
    public function getAllUserCards(): Collection;
}
