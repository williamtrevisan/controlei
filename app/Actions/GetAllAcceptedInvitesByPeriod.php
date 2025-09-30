<?php

namespace App\Actions;

use App\Repositories\Contracts\InviteRepository;
use Illuminate\Support\Collection;

class GetAllAcceptedInvitesByPeriod
{
    public function __construct(
        private InviteRepository $inviteRepository
    ) {}

    public function execute(int $userId, int $days): Collection
    {
        return $this->inviteRepository->findAcceptedByUserAndPeriod($userId, $days);
    }
}
