<?php

namespace App\Actions;

use App\Repositories\Contracts\InviteRepository;
use Illuminate\Support\Collection;

class GetAllPendingInvites
{
    public function __construct(
        private InviteRepository $inviteRepository
    ) {}

    public function execute(int $userId): Collection
    {
        return $this->inviteRepository->findPendingByUser($userId);
    }
}
