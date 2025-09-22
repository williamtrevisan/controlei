<?php

namespace App\Actions;

use App\Repositories\Contracts\TransactionRepository;
use App\ValueObjects\StatementPeriod;
use Illuminate\Database\Eloquent\Collection;

class GetAllIncomeTransactionsByStatementPeriod
{
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {}

    public function execute(StatementPeriod $statementPeriod): Collection
    {
        return $this->transactionRepository->findIncomesByStatementPeriod($statementPeriod);
    }
}
