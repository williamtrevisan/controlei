<?php

namespace App\Notifications;

use App\Actions\AcceptInvite;
use App\Actions\RejectInvite;
use App\Models\Invite;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InviteReceived extends Notification implements ShouldQueue
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
            ->title('Novo convite recebido')
            ->body(sprintf(
                '%s enviou um convite para você%s',
                $this->invite->inviter->name,
                $this->invite->message ? ': ' . $this->invite->message : ''
            ))
            ->icon('heroicon-o-user-plus')
            ->actions([
                Action::make('accept')
                    ->label('Aceitar')
                    ->color('primary')
                    ->button()
                    ->markAsRead()
                    ->url(fn (): string => route('invites.accept', ['invite' => $this->invite->id]))
                    ->postToUrl(),

                Action::make('reject')
                    ->label('Recusar')
                    ->color('gray')
                    ->button()
                    ->markAsRead()
                    ->url(fn (): string => route('invites.reject', ['invite' => $this->invite->id]))
                    ->postToUrl(),
            ])
            ->getDatabaseMessage();
    }
}

