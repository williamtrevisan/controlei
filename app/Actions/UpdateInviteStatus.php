<?php

namespace App\Actions;

use App\Enums\InvitationStatus;
use App\Models\Invite;
use App\Repositories\Contracts\InviteRepository;

class UpdateInviteStatus
{
    public function __construct(
        private readonly InviteRepository $inviteRepository,
    ) {
    }

    public function execute(Invite $invite, InvitationStatus $status): Invite
    {
        return $this->inviteRepository->update($invite, [
            'status' => $status,
        ]);
    }
}

