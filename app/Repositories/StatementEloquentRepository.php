<?php

namespace App\Repositories;

use App\DataTransferObjects\TransactionData;
use App\Models\Statement;
use App\Repositories\Contracts\StatementRepository;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class StatementEloquentRepository implements StatementRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {
    }

    protected function builder(): Builder
    {
        return $this->model
            ->newQuery()
            ->whereHas('account', fn(Builder $query) => $query->where('user_id', auth()->id()));
    }

    /**
     * @param Collection<int, TransactionData> $values
     * @return bool
     */
    public function createMany(Collection $values): bool
    {
        return $this->builder()
            ->insert($values->toArray());
    }

    /**
     * @param string|Closure $column
     * @param mixed $value
     * @return Collection<int, Statement>
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
     * @return Collection<string, Statement>
     */
    public function getAllUserStatements(): Collection
    {
        return $this->builder()
            ->get();
    }
}
