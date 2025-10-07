<?php

namespace App\Observers;

use App\Enums\InvitationStatus;
use App\Models\Invite;

class InviteObserver
{
    public function updating(Invite $invite): void
    {
        if ($invite->isDirty('status') && $invite->status === InvitationStatus::Accepted) {
            $invite->accepted_at = now();
        }
    }
}

