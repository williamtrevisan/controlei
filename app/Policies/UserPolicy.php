<?php

namespace App\Policies;

use App\Actions\GetUserImportUsage;
use App\Actions\GetUserSynchronizationUsage;
use App\Models\User;

class UserPolicy
{
    public function __construct(
        private readonly GetUserSynchronizationUsage $getUserSynchronizationUsage,
        private readonly GetUserImportUsage $getUserImportUsage
    ) {}

    public function synchronization(User $user): bool
    {
        $usage = $this->getUserSynchronizationUsage->execute($user);
        if (is_null($usage->limit)) {
            return true;
        }

        return $usage->current < $usage->limit;
    }

    public function import(User $user): bool
    {
        $usage = $this->getUserImportUsage->execute($user);
        if (is_null($usage->limit)) {
            return true;
        }
        
        return $usage->current < $usage->limit;
    }
}
