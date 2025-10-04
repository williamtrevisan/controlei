<?php

namespace App\Actions;

use App\DataTransferObjects\CardData;
use App\Models\Account;
use App\Models\Card;
use App\Repositories\Contracts\CardRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GetAllUsersCardByLastFourDigitsAndAccount
{
    public function __construct(
        private readonly CardRepository $cardRepository,
    ) {
    }

    public function execute(Collection $lastFourDigits, Account $account): Collection
    {
        return $this->cardRepository->findManyBy(function (Builder $query) use ($lastFourDigits, $account) {
            $query->where('account_id', $account->id)
                ->whereIn('last_four_digits', $lastFourDigits);
        });
    }
}
