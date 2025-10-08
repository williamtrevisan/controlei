<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\DataTransferObjects\SynchronizationData;
use App\DataTransferObjects\TransactionData;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

readonly class AssignParentTransaction
{
    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $parents = $this->parents($data->transactions);

        $transactions = $data->transactions
            ->map(function (TransactionData $transaction) use ($parents): TransactionData {
                return is_null($parentId = $parents->get($transaction->id))
                    ? $transaction
                    : $transaction->withParentTransactionId($parentId);
            });

        return $next($data->withTransactions($transactions));
    }

    private function parents(LazyCollection $transactions): Collection
    {
        return $transactions
            ->filter(fn (TransactionData $transaction) => $transaction->totalInstallments > 1)
            ->collect()
            ->groupBy(fn (TransactionData $transaction) => $this->installmentSignature($transaction))
            ->filter(fn (Collection $groups) => $groups->count() > 1)
            ->flatMap(function (Collection $groups) {
                $parent = $groups->firstWhere('currentInstallment', 1)
                    ?? $groups->sortBy('currentInstallment')->first();

                return $groups
                    ->reject(fn (TransactionData $transaction) => $transaction->id === $parent->id)
                    ->mapWithKeys(fn (TransactionData $transaction) => [$transaction->id => $parent->id]);
            });
    }

    private function installmentSignature(TransactionData $transaction): string
    {
        return hash('sha256', implode('|', [
            $transaction->accountId,
            $transaction->cardId,
            $this->description($transaction->description),
            $transaction->amount,
            $transaction->totalInstallments,
        ]));
    }

    private function description(string $description): string
    {
        return str($description)
            ->limit(15, '')
            ->value();
    }
}

