<?php

namespace App\Actions;

use App\Repositories\Contracts\InviteRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetAllUserSentInvitesByPeriod
{
    public function __construct(
        private InviteRepository $inviteRepository
    ) {}

    public function execute(Carbon $days): Collection
    {
        return $this->inviteRepository->sentByPeriod($days);
    }
}
