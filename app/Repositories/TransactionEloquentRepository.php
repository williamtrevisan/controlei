<?php

namespace App\Repositories;

use App\DataTransferObjects\TransactionData;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepository;
use App\ValueObjects\StatementPeriod;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class TransactionEloquentRepository implements TransactionRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {
    }

    protected function builder(): Builder
    {
        return $this->model
            ->newQuery()
            ->where(function (Builder $query) {
                $query->whereHas('account', function (Builder $subQuery) {
                    $subQuery->where('user_id', auth()->id());
                })
                ->orWhereHas('members', function (Builder $subQuery) {
                    $subQuery->where('member_id', auth()->id());
                });
            });
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function all(): Collection
    {
        return $this->builder()
            ->get();
    }

    public function existsBy(string $column, mixed $value): bool
    {
        return $this->builder()
            ->where($column, $value)
            ->exists();
    }

    /**
     * @param LazyCollection<int, TransactionData> $values
     * @return bool
     */
    public function createMany(LazyCollection $values): bool
    {
        return $this->builder()
            ->insert($values->toArray());
    }

    /**
     * @param string|Closure $column
     * @param mixed $value
     * @return Collection<int, Transaction>
     */
    public function findManyBy(string|Closure $column, mixed $value = null): Collection
    {
        return $this->builder()
            ->when(
                $column instanceof Closure,
                fn (Builder $query) => $column($query),
                fn (Builder $query) => $query->where($column, $value)
            )
            ->get();
    }

    /**
     * @param StatementPeriod $statementPeriod
     * @return Collection<int, Transaction>
     */
    public function findIncomesByStatementPeriod(StatementPeriod $statementPeriod): Collection
    {
        return $this->findManyBy(function (Builder $query) use ($statementPeriod) {
            $query
                ->where('direction', 'inflow')
                ->where('statement_period', $statementPeriod->value());
        });
    }

    /**
     * @param StatementPeriod $statementPeriod
     * @return Collection<int, Transaction>
     */
    public function findExpensesByStatementPeriod(StatementPeriod $statementPeriod): Collection
    {
        return $this->findManyBy(function (Builder $query) use ($statementPeriod) {
            $query
                ->where(function (Builder $query) {
                    $query->where('direction', 'outflow')
                        ->orWhere(function (Builder $query) {
                            $query
                                ->where('direction', 'inflow')
                                ->where('kind', 'refund');
                        });
                })
                ->where('kind', 'purchase')
                ->where('statement_period', $statementPeriod->value());
        });
    }

    /**
     * @param Transaction $transaction
     * @return Collection<int, Transaction>
     */
    public function getAllInstallments(Transaction $transaction): Collection
    {
        if ($transaction->parent_transaction_id) {
            return Transaction::where('parent_transaction_id', $transaction->parent_transaction_id)
                ->orWhere('id', $transaction->parent_transaction_id)
                ->get();
        }

        return Transaction::where('parent_transaction_id', $transaction->id)
            ->orWhere('id', $transaction->id)
            ->get();
    }
}
