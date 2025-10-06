<?php

namespace App\Actions;

use App\Models\Invite;
use App\Repositories\Contracts\InviteRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetAllUserAcceptedInvitesByPeriod
{
    public function __construct(
        private InviteRepository $inviteRepository
    ) {}

    /**
     * @param Carbon $date
     * @return Collection<int, Invite>
     */
    public function execute(Carbon $date): Collection
    {
        return $this->inviteRepository->acceptedByPeriod($date);
    }
}
