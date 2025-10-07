<?php

namespace App\Http\Controllers;

use App\Actions\AcceptInvite;
use App\Actions\RemoveInviteActionsFromNotification;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Invite;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;

class AcceptInviteController extends Controller
{
    public function __invoke(
        Invite $invite,
        AcceptInvite $acceptInvite,
        RemoveInviteActionsFromNotification $removeInviteActionsFromNotification
    ): RedirectResponse {
        $acceptInvite->execute($invite);
        $removeInviteActionsFromNotification->execute($invite);

        Notification::make()
            ->title('Convite aceito')
            ->body(sprintf('Você agora está conectado com %s!', $invite->inviter->name))
            ->success()
            ->send();

        return redirect()
            ->to(TransactionResource::getUrl('index'));
    }
}

