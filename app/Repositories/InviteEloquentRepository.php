<?php

namespace App\Repositories;

use App\Enums\InvitationStatus;
use App\Models\Invite;
use App\Models\User;
use App\Repositories\Contracts\InviteRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class InviteEloquentRepository implements InviteRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {
    }

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @param string $userId
     * @param int $days
     * @return Collection<int, Invite>
     */
    public function findSentByUserAndPeriod(string $userId, int $days): Collection
    {
        return $this->builder()
            ->where('inviter_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @param string $userId
     * @return Collection<int, Invite>
     */
    public function findPendingByUser(string $userId): Collection
    {
        return $this->builder()
            ->where('inviter_id', $userId)
            ->where('status', InvitationStatus::Pending)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @param string $userId
     * @param int $days
     * @return Collection<int, Invite>
     */
    public function findAcceptedByUserAndPeriod(string $userId, int $days): Collection
    {
        return $this->builder()
            ->where('inviter_id', $userId)
            ->where('status', InvitationStatus::Accepted)
            ->where('accepted_at', '>=', now()->subDays($days))
            ->orderBy('accepted_at')
            ->get();
    }

    /**
     * @param string $userId
     * @return Collection<int, User>
     */
    public function findConnectedUsers(string $userId): Collection
    {
        $usersWhoAcceptedMyInvites = $this->builder()
            ->where('inviter_id', $userId)
            ->where('status', InvitationStatus::Accepted)
            ->with('invitee')
            ->get()
            ->pluck('invitee');

        $usersWhoseInvitesIAccepted = $this->builder()
            ->where('invitee_id', $userId)
            ->where('status', InvitationStatus::Accepted)
            ->with('inviter')
            ->get()
            ->pluck('inviter');

        return $usersWhoAcceptedMyInvites
            ->merge($usersWhoseInvitesIAccepted)
            ->unique('id');
    }
}
