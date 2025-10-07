<?php

namespace App\Notifications;

use App\Models\Invite;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InviteSent extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Invite $invite
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Convite enviado')
            ->body(sprintf(
                'Seu convite foi enviado para %s%s',
                $this->invite->invitee->name,
                $this->invite->message ? ' com a mensagem: "' . $this->invite->message . '"' : ''
            ))
            ->icon('heroicon-o-paper-airplane')
            ->success()
            ->getDatabaseMessage();
    }
}

