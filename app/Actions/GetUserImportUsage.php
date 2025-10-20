<?php

namespace App\Actions;

use App\DataTransferObjects\Usage;
use App\Enums\ResourceType;
use App\Models\User;
use App\Repositories\Contracts\UsageTrackingRepository;

readonly class GetUserImportUsage
{
    public function __construct(
        private UsageTrackingRepository $usageTrackingRepository
    ) {}

    public function execute(User $user): Usage
    {
        $plan = $user->plan;
        $current = $this->usageTrackingRepository->usage($user, ResourceType::Import);

        return new Usage(
            current: $current,
            limit: $plan->max_imports_per_month,
            percentage: $plan->max_imports_per_month
                ? ($current / $plan->max_imports_per_month) * 100
                : 0,
        );
    }
}

