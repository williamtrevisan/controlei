<?php

namespace App\Observers;

use App\Actions\CreateUserImportUsage;
use App\Actions\CreateUserSynchronizationUsage;
use App\Actions\GetAllPlans;
use App\Models\User;

readonly class UserObserver
{
    public function __construct(
        private GetAllPlans $getAllPlans,
        private CreateUserSynchronizationUsage $createUserSynchronizationUsage,
        private CreateUserImportUsage $createUserImportUsage,
    ) {}

    public function creating(User $user): void
    {
        if (is_null($user->invite_code)) {
            $user->invite_code = $user->inviteCode();
        }

        if (is_null($user->plan_id)) {
            $user->plan_id = $this->getAllPlans
                ->execute()
                ->where('slug', 'free')
                ->first()
                ?->id;
        }
    }

    public function created(User $user): void
    {
        $this->createUserSynchronizationUsage->execute($user);
        $this->createUserImportUsage->execute($user);
    }
}
