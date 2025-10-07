<?php

namespace App\Actions;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;

class FindInviteeById
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function execute(string $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }
}

