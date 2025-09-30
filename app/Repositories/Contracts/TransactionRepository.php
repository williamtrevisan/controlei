<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\TransactionData;
use App\Models\Transaction;
use App\ValueObjects\StatementPeriod;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

interface TransactionRepository
{
    /**
     * @return Collection<int, Transaction>
     */
    public function all(): Collection;

    public function existsBy(string $column, mixed $value): bool;

    /**
     * @param LazyCollection<int, TransactionData> $values
     * @return bool
     */
    public function createMany(LazyCollection $values): bool;

    /**
     * @param string|Closure $column
     * @param mixed $value
     * @return Collection<int, Transaction>
     */
    public function findManyBy(string|\Closure $column, mixed $value = null): Collection;

    /**
     * @param StatementPeriod $statementPeriod
     * @return Collection<int, Transaction>
     */
    public function findIncomesByStatementPeriod(StatementPeriod $statementPeriod): Collection;
    
    /**
     * @param StatementPeriod $statementPeriod
     * @return Collection<int, Transaction>
     */
    public function findExpensesByStatementPeriod(StatementPeriod $statementPeriod): Collection;

    /**
     * @param Transaction $transaction
     * @return Collection<int, Transaction>
     */
    public function getAllInstallments(Transaction $transaction): Collection;
}
