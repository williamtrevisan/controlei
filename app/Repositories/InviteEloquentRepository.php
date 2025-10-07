<?php

namespace App\Repositories;

use App\Enums\InvitationStatus;
use App\Models\Invite;
use App\Models\User;
use App\Repositories\Contracts\InviteRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
     * @return Collection<int, Invite>
     */
    public function received(): Collection
    {
        return $this->builder()
            ->where('invitee_id', auth()->id())
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return Collection<int, Invite>
     */
    public function sent(): Collection
    {
        return $this->builder()
            ->where('inviter_id', auth()->id())
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @param Carbon $date
     * @return Collection<int, Invite>
     */
    public function sentByPeriod(Carbon $date): Collection
    {
        return $this->builder()
            ->where('inviter_id', auth()->id())
            ->where('created_at', '>=', $date)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return Collection<int, Invite>
     */
    public function pending(): Collection
    {
        return $this->builder()
            ->where('inviter_id', auth()->id())
            ->where('status', InvitationStatus::Pending)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @param Carbon $date
     * @return Collection<int, Invite>
     */
    public function acceptedByPeriod(Carbon $date): Collection
    {
        return $this->builder()
            ->where('inviter_id', auth()->id())
            ->where('status', InvitationStatus::Accepted)
            ->where('accepted_at', '>=', $date)
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

    public function exists(User $invitee): bool
    {
        return $this->builder()
            ->where('inviter_id', auth()->id())
            ->where('invitee_id', $invitee->id)
            ->exists();
    }

    public function update(Invite $invite, array $data): Invite
    {
        return tap($invite)
            ->update($data);
    }
}
