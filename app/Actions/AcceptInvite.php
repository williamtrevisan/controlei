<?php

namespace App\Actions;

use App\Enums\InvitationStatus;
use App\Models\Invite;

class AcceptInvite
{
    public function __construct(
        private readonly UpdateInviteStatus $updateInviteStatus,
    ) {
    }

    public function execute(Invite $invite): Invite
    {
        return $this->updateInviteStatus->execute($invite, InvitationStatus::Accepted);
    }
}

