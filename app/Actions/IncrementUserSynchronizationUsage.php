<?php

namespace App\Actions;

use App\Models\User;
use App\Repositories\Contracts\UsageTrackingRepository;

readonly class IncrementUserSynchronizationUsage
{
    public function __construct(
        private FindUserSynchronizationUsage $findUserSynchronizationUsage,
        private UsageTrackingRepository $usageTrackingRepository
    ) {}

    public function execute(User $user): void
    {
        $this->usageTrackingRepository
            ->increment($this->findUserSynchronizationUsage->execute($user));
    }
}

