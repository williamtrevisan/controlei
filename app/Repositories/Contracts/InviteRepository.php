<?php

namespace App\Repositories\Contracts;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface InviteRepository
{
    /**
     * @return Collection<int, Invite>
     */
    public function received(): Collection;

    /**
     * @return Collection<int, Invite>
     */
    public function sent(): Collection;

    /**
     * @param Carbon $date
     * @return Collection<int, Invite>
     */
    public function sentByPeriod(Carbon $date): Collection;

    /**
     * @return Collection<int, Invite>
     */
    public function pending(): Collection;

    /**
     * @param Carbon $date
     * @return Collection<int, Invite>
     */
    public function acceptedByPeriod(Carbon $date): Collection;

    /**
     * @param string $userId
     * @return Collection<int, User>
     */
    public function findConnectedUsers(string $userId): Collection;
}
