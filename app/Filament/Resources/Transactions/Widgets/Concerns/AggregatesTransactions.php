<?php

namespace App\Filament\Resources\Transactions\Widgets\Concerns;

use App\Models\Transaction;
use Brick\Money\Money;
use Illuminate\Support\Collection;

trait AggregatesTransactions
{
    /**
     * @param Collection $transactions
     * @return Collection<string, Money>
     */
    public function aggregateByDay(Collection $transactions): Collection
    {
        return $transactions
            ->mapToGroups(fn (Transaction $transaction) => [
                $transaction->date->format('Y-m-d') => $transaction->amount,
            ])
            ->map(function (Collection $amounts) {
                return $amounts
                    ->reduce(fn (Money $carry, Money $amount) => $carry->plus($amount), money()->of(0));
            });
    }
}
