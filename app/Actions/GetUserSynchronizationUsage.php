<?php

namespace App\Actions;

use App\DataTransferObjects\Usage;
use App\Enums\ResourceType;
use App\Models\User;
use App\Repositories\Contracts\UsageTrackingRepository;

readonly class GetUserSynchronizationUsage
{
    public function __construct(
        private UsageTrackingRepository $usageTrackingRepository
    ) {}

    public function execute(User $user): Usage
    {
        $plan = $user->plan;
        $current = $this->usageTrackingRepository->usage($user, ResourceType::Synchronization);

        return new Usage(
            current: $current,
            limit: $plan->max_synchronizations_per_month,
            percentage: ($current / $plan->max_synchronizations_per_month) * 100,
        );
    }
}

