<?php

namespace App\Repositories\Contracts;

use App\Models\Plan;
use Illuminate\Support\Collection;

interface PlanRepository
{
    public function findBySlug(string $slug): ?Plan;

    /**
     * @return Collection<int, Plan>
     */
    public function actives(): Collection;
}

