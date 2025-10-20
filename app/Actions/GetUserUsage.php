<?php

namespace App\Actions;

use App\DataTransferObjects\UserUsageData;
use App\Models\User;

readonly class GetUserUsage
{
    public function __construct(
        private GetUserSynchronizationUsage $getUserSynchronizationUsage,
        private GetUserImportUsage $getUserImportUsage
    ) {}

    public function execute(User $user): UserUsageData
    {
        return UserUsageData::from($user, $this->getUserSynchronizationUsage, $this->getUserImportUsage);
    }
}

