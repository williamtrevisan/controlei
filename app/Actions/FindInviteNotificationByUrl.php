<?php

namespace App\Actions;

use App\Models\Invite;
use App\Notifications\InviteReceived;
use App\Repositories\Contracts\NotificationRepository;
use Illuminate\Notifications\DatabaseNotification;

class FindInviteNotificationByUrl
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    public function execute(Invite $invite): ?DatabaseNotification
    {
        return $this->notificationRepository
            ->findByUserAndType($invite->invitee, InviteReceived::class)
            ->first(function ($notification) use ($invite): bool {
                return collect($notification->data['actions'] ?? [])
                    ->some(fn ($action): bool => str_contains($action['url'] ?? '', "/invites/$invite->id/"));
            });
    }
}

