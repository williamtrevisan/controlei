<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\TransactionData;
use App\Models\Statement;
use Closure;
use Illuminate\Support\Collection;

interface StatementRepository
{
    /**
     * @param Collection<int, TransactionData> $values
     * @return bool
     */
    public function createMany(Collection $values): bool;

    /**
     * @param string|Closure $column
     * @param mixed $value
     * @return Collection<int, Statement>
     */
    public function findManyBy(string|Closure $column, mixed $value = null): Collection;

    /**
     * @return Collection<string, Statement>
     */
    public function getAllUserStatements(): Collection;
}
