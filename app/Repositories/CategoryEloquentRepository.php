<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CategoryEloquentRepository implements CategoryRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {
    }

    protected function builder(): Builder
    {
        return $this->model
            ->newQuery();
    }

    public function actives(): Collection
    {
        return $this->builder()
            ->where('active', true)
            ->get();
    }

    public function findById(int $id): ?Category
    {
        return $this->builder()
            ->find($id);
    }

    public function findByDescription(string $description): ?Category
    {
        return $this->builder()
            ->where('description', $description)
            ->first();
    }
}
