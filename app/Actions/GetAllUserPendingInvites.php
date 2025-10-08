<?php

namespace App\Actions;

use App\Models\Invite;
use App\Repositories\Contracts\InviteRepository;
use Illuminate\Support\Collection;

class GetAllUserPendingInvites
{
    public function __construct(
        private InviteRepository $inviteRepository
    ) {}

    /**
     * @return Collection<int, Invite>
     */
    public function execute(): Collection
    {
        return $this->inviteRepository->pending();
    }
}
