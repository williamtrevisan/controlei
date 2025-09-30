<?php

namespace App\Filament\Resources\Invites\Pages;

use App\Filament\Resources\Invites\InviteResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Find user by invite code
        $user = \App\Models\User::where('invite_code', strtoupper($data['invite_code']))->firstOrFail();
        
        return [
            'inviter_id' => auth()->id(),
            'invitee_id' => $user->id,
            'message' => $data['message'] ?? null,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        $inviteeName = $this->getRecord()->invitee->name;
        
        return Notification::make()
            ->success()
            ->title('Convite enviado com sucesso!')
            ->body("Seu convite foi enviado para **{$inviteeName}**.")
            ->duration(5000);
    }
}
