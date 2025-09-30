<?php

namespace App\Repositories\Contracts;

use App\Enums\InvitationStatus;
use Illuminate\Support\Collection;

interface InviteRepository
{
    /**
     * @param int $userId
     * @param int $days
     * @return Collection<int, Invite>
     */
    public function findSentByUserAndPeriod(int $userId, int $days): Collection;

    /**
     * @param int $userId
     * @return Collection<int, Invite>
     */
    public function findPendingByUser(int $userId): Collection;

    /**
     * @param int $userId
     * @param int $days
     * @return Collection<int, Invite>
     */
    public function findAcceptedByUserAndPeriod(int $userId, int $days): Collection;

    /**
     * @param int $userId
     * @return Collection<int, User>
     */
    public function findConnectedUsers(int $userId): Collection;
}
