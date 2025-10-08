<?php

namespace App\Actions;

use App\Repositories\Contracts\TransactionRepository;
use Illuminate\Support\Collection;

class GetAllUserExpenseTransactions
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function execute(): Collection
    {
        return $this->transactionRepository->expenses();
    }
}

