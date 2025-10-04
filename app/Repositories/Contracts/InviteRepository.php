<?php

namespace App\Repositories\Contracts;

use App\Enums\InvitationStatus;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Collection;

interface InviteRepository
{
    /**
     * @param string $userId
     * @param int $days
     * @return Collection<int, Invite>
     */
    public function findSentByUserAndPeriod(string $userId, int $days): Collection;

    /**
     * @param string $userId
     * @return Collection<int, Invite>
     */
    public function findPendingByUser(string $userId): Collection;

    /**
     * @param string $userId
     * @param int $days
     * @return Collection<int, Invite>
     */
    public function findAcceptedByUserAndPeriod(string $userId, int $days): Collection;

    /**
     * @param string $userId
     * @return Collection<int, User>
     */
    public function findConnectedUsers(string $userId): Collection;
}
