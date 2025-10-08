<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function creating(User $user): void
    {
        if (is_null($user->invite_code)) {
            $user->invite_code = $user->inviteCode();
        }
    }
}
