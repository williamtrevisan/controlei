<?php

namespace App\Actions;

use App\Repositories\Contracts\PlanRepository;
use Illuminate\Support\Collection;

class GetAllPlans
{
    public function __construct(
        private readonly PlanRepository $planRepository
    ) {}

    public function execute(): Collection
    {
        return $this->planRepository->actives();
    }
}

