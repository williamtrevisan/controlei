<?php

namespace App\Actions;

use App\DataTransferObjects\CardData;
use App\Models\Account;
use App\Models\Card;
use Illuminate\Support\Collection;

readonly class FindManyCards
{
    public function __construct(
        private GetAllUsersCardByLastFourDigitsAndAccount $getAllUsersCardByLastFourDigitsAndAccount,
    ) {
    }

    public function execute(Collection $cards, Account $account): Collection
    {
        if ($cards->isEmpty()) {
            return collect();
        }

        return $this->getAllUsersCardByLastFourDigitsAndAccount
            ->execute($cards->pluck('lastFourDigits')->unique(), $account);
    }
}
