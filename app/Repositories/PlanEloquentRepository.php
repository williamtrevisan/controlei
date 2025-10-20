<?php

namespace App\Repositories;

use App\Models\Plan;
use App\Repositories\Contracts\PlanRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PlanEloquentRepository implements PlanRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {}

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    public function findBySlug(string $slug): ?Plan
    {
        return $this->builder()
            ->where('slug', $slug)
            ->where('active', true)
            ->first();
    }

    public function actives(): Collection
    {
        return $this->builder()
            ->where('active', true)
            ->get();
    }

    public function freePlan(): Plan
    {
        return $this->builder()
            ->where('slug', 'free')
            ->where('active', true)
            ->firstOrFail();
    }

    public function basicPlan(): Plan
    {
        return $this->builder()
            ->where('slug', 'basic')
            ->where('active', true)
            ->firstOrFail();
    }
}

