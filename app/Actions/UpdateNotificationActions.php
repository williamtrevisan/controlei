<?php

namespace App\Actions;

use App\Repositories\Contracts\NotificationRepository;
use Illuminate\Notifications\DatabaseNotification;

class UpdateNotificationActions
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    public function execute(DatabaseNotification $notification, array $actions): DatabaseNotification
    {
        $data = $notification->data;
        $data['actions'] = $actions;

        return $this->notificationRepository->update($notification, [
            'data' => $data,
        ]);
    }
}

