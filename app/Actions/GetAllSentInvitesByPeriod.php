<?php

namespace App\Actions;

use App\Repositories\Contracts\InviteRepository;
use Illuminate\Support\Collection;

class GetAllSentInvitesByPeriod
{
    public function __construct(
        private InviteRepository $inviteRepository
    ) {}

    public function execute(int $userId, int $days): Collection
    {
        return $this->inviteRepository->findSentByUserAndPeriod($userId, $days);
    }
}
