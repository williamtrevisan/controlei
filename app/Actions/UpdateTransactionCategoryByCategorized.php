<?php

namespace App\Actions;

use App\DataTransferObjects\CategorizedTransactionData;
use App\Repositories\Contracts\TransactionRepository;
use Illuminate\Support\Collection;

class UpdateTransactionCategoryByCategorized
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    /**
     * @param Collection<int, CategorizedTransactionData> $categorizedTransactions
     * @return void
     */
    public function execute(Collection $categorizedTransactions): void
    {
        $categorizedTransactions
            ->each(function (CategorizedTransactionData $data): void {
                $this->transactionRepository->update($data->id, [
                    'category_id' => $data->categoryId,
                ]);
            });
    }
}

