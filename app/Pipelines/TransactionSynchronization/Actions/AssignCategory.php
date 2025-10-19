<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\Actions\CategorizeManyTransactions;
use App\DataTransferObjects\SynchronizationData;
use App\DataTransferObjects\TransactionData;
use Closure;

readonly class AssignCategory
{
    public function __construct(
        private CategorizeManyTransactions $categorizeTransaction,
    ) {}

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $categorizedTransactions = $this->categorizeTransaction->execute($data->transactions->collect());

        $transactions = $data->transactions
            ->map(function (TransactionData $transaction) use ($categorizedTransactions): TransactionData {
                $categorized = $categorizedTransactions->firstWhere('id', $transaction->id);

                return $transaction->withCategoryId($categorized?->categoryId ?? 8);
            });

        return $next($data->withTransactions($transactions));
    }
}
