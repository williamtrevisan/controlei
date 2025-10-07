<?php

namespace App\Actions;

use App\Models\Invite;

class RemoveInviteActionsFromNotification
{
    public function __construct(
        private readonly FindInviteNotificationByUrl $findInviteNotificationByUrl,
        private readonly UpdateNotificationActions $updateNotificationActions,
    ) {
    }

    public function execute(Invite $invite): void
    {
        $notification = $this->findInviteNotificationByUrl->execute($invite);
        if (! $notification) {
            return;
        }

        $this->updateNotificationActions->execute($notification, []);
    }
}

