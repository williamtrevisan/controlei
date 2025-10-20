<?php

namespace App\Actions;

use App\Models\User;
use App\Repositories\Contracts\UsageTrackingRepository;

readonly class IncrementUserImportUsage
{
    public function __construct(
        private FindUserImportUsage $findUserImportUsage,
        private UsageTrackingRepository $usageTrackingRepository
    ) {}

    public function execute(User $user): void
    {
        $this->usageTrackingRepository
            ->increment($this->findUserImportUsage->execute($user));
    }
}

