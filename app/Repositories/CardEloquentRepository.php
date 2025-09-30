<?php

namespace App\Repositories;

use App\DataTransferObjects\CardData;
use App\Models\Card;
use App\Repositories\Contracts\CardRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CardEloquentRepository implements CardRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {
    }

    protected function builder(): Builder
    {
        return $this->model
            ->newQuery()
            ->whereHas('account', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
    }

    public function findOrCreate(CardData $card): Card
    {
        return $this->builder()
            ->firstOrCreate([
                'account_id' => $card->accountId,
                'last_four_digits' => $card->lastFourDigits,
            ], [
                'type' => $card->type,
                'brand' => $card->brand?->value,
                'limit' => $card->limit,
                'due_day' => $card->dueDay,
                'matcher_regex' => $card->matcherRegex,
            ]);
    }

    /**
     * @return Collection<int, Card>
     */
    public function getAllMatcherRegex(): Collection
    {
        return $this->builder()
            ->whereNotNull('matcher_regex')
            ->pluck('matcher_regex');
    }

    /**
     * @return Collection<int, Card>
     */
    public function getAllUserCards(): Collection
    {
        return $this->builder()
            ->whereHas('account', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with('account')
            ->get();
    }
}
