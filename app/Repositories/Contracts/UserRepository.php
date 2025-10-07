<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepository
{
    /**
     * @param string $inviteCode
     * @return Collection<int, User>
     */
    public function findByInviteCode(string $inviteCode): Collection;

    public function findById(string $userId): ?User;
}

