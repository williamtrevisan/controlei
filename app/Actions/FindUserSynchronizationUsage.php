<?php

namespace App\Actions;

use App\Enums\ResourceType;
use App\Models\User;
use App\Models\UsageTracking;
use App\Repositories\Contracts\UsageTrackingRepository;

readonly class FindUserSynchronizationUsage
{
    public function __construct(
        private UsageTrackingRepository $usageTrackingRepository
    ) {}

    public function execute(User $user): ?UsageTracking
    {
        return $this->usageTrackingRepository->find($user, ResourceType::Synchronization);
    }
}

