<?php

namespace App\Actions;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepository;
use App\ValueObjects\StatementPeriod;
use Illuminate\Support\Collection;

class GetAllExpenseTransactionsByStatementPeriod
{
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {}

    /**
     * @param StatementPeriod $statementPeriod
     * @return Collection<int, Transaction>
     */
    public function execute(StatementPeriod $statementPeriod): Collection
    {
        return $this->transactionRepository->findExpensesByStatementPeriod($statementPeriod);
    }
}
