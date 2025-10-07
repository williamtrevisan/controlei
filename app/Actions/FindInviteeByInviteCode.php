<?php

namespace App\Actions;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Collection;

class FindInviteeByInviteCode
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @param string $inviteCode
     * @return Collection<int, User>
     */
    public function execute(string $inviteCode): Collection
    {
        return $this->userRepository->findByInviteCode($inviteCode);
    }
}
