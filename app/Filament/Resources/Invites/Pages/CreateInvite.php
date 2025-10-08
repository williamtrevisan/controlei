<?php

namespace App\Filament\Resources\Invites\Pages;

use App\Filament\Resources\Invites\InviteResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteReceived;
use App\Notifications\InviteSent;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateInvite extends CreateRecord
{
    protected static string $resource = InviteResource::class;

    public function getBreadcrumb(): string
    {
        return 'Enviar convite';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Enviar convite';
    }

    public function getSubheading(): ?string
    {
        return 'Envie um convite para outro usuário usando o código dele.';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Convite enviado com sucesso!';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            'inviter_id' => auth()->id(),
            'invitee_id' => $data['invitee_id'],
            'message' => $data['message'] ?? null,
        ];
    }

    protected function afterCreate(): void
    {
        /** @var Invite $invite */
        $invite = $this->getRecord();

        $invite->invitee->notify(new InviteReceived($invite));
        $invite->inviter->notify(new InviteSent($invite));
    }

    protected function getRedirectUrl(): string
    {
        return TransactionResource::getUrl('index');
    }
}

