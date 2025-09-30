<?php

namespace App\Repositories;

use App\Models\IncomeSource;
use App\Repositories\Contracts\IncomeSourceRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class IncomeSourceEloquentRepository implements IncomeSourceRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {
    }

    protected function builder(): Builder
    {
        return $this->model
            ->newQuery()
            ->where('user_id', auth()->id());
    }

    public function getActiveForMatching(): Collection
    {
        return $this->builder()
            ->where('active', true)
            ->get(['id', 'matcher_regex']);
    }
}
