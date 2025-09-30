<?php

namespace App\Actions;

use App\Models\User;
use App\Repositories\Contracts\InviteRepository;
use Illuminate\Support\Collection;

class GetAllConnectedUsers
{
    public function __construct(
        private InviteRepository $inviteRepository
    ) {}

    /**
     * @param ?User $user
     * @return Collection<int, User>
     */
    public function execute(?User $user = null): Collection
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return collect();
        }
        
        return $this->inviteRepository->findConnectedUsers($user->id);
    }
}
