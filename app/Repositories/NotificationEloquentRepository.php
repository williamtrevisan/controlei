<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\NotificationRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class NotificationEloquentRepository implements NotificationRepository
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
     * @param User $user
     * @param string $type
     * @return Collection<int, DatabaseNotification>
     */
    public function findByUserAndType(User $user, string $type): Collection
    {
        return $this->builder()
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->where('type', $type)
            ->get();
    }

    public function update(DatabaseNotification $notification, array $data): DatabaseNotification
    {
        return tap($notification)->update($data);
    }
}

