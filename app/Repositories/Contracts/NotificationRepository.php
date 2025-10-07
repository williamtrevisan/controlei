<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

interface NotificationRepository
{
    /**
     * @param User $user
     * @param string $type
     * @return Collection<int, DatabaseNotification>
     */
    public function findByUserAndType(User $user, string $type): Collection;

    public function update(DatabaseNotification $notification, array $data): DatabaseNotification;
}

