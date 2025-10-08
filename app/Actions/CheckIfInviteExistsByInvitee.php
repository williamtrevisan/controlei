<?php

namespace App\Actions;

use App\Models\User;
use App\Repositories\Contracts\InviteRepository;

class CheckIfInviteExistsByInvitee
{
    public function __construct(
        private readonly InviteRepository $inviteRepository,
    ) {
    }

    public function execute(User $invitee): bool
    {
        return $this->inviteRepository->exists($invitee);
    }
}

