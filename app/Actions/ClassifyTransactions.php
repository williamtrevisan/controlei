<?php

namespace App\Actions;

use App\Models\Transaction;

class ClassifyTransactions
{
    public function __construct(
        private readonly GetAllTransactions $getAllTransactions,
    ) {
    }

    public function execute(): void
    {
        $this->getAllTransactions
            ->execute()
            ->map(fn (Transaction $transaction): bool => $transaction->classify()->save());
    }
}
