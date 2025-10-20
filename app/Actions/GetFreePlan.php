<?php

namespace App\Actions;

use App\Models\Plan;
use App\Repositories\Contracts\PlanRepository;

readonly class GetFreePlan
{
    public function __construct(
        private PlanRepository $planRepository
    ) {}

    public function execute(): Plan
    {
        return $this->planRepository->freePlan();
    }
}

