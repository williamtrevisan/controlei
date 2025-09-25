<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Collection;

interface CategoryRepository
{
    public function actives(): Collection;

    public function findById(int $id): ?Category;

    public function findByDescription(string $description): ?Category;
}
