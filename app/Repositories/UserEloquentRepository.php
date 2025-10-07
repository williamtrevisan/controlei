<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserEloquentRepository implements UserRepository
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
     * @param string $inviteCode
     * @return Collection<int, User>
     */
    public function findByInviteCode(string $inviteCode): Collection
    {
        return $this->builder()
            ->where('invite_code', strtoupper($inviteCode))
            ->get();
    }

    public function findById(string $userId): ?User
    {
        return $this->builder()
            ->where('id', $userId)
            ->first();
    }
}

