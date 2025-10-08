<?php

namespace App\Actions;

use App\Repositories\Contracts\InviteRepository;
use Illuminate\Support\Collection;

class GetAllUserSentInvites
{
    public function __construct(
        private InviteRepository $inviteRepository
    ) {}

    public function execute(): Collection
    {
        return $this->inviteRepository->sent();
    }
}
