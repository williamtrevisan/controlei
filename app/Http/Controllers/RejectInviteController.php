<?php

namespace App\Http\Controllers;

use App\Actions\RejectInvite;
use App\Actions\RemoveInviteActionsFromNotification;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Invite;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;

class RejectInviteController extends Controller
{
    public function __invoke(
        Invite $invite,
        RejectInvite $rejectInvite,
        RemoveInviteActionsFromNotification $removeInviteActionsFromNotification
    ): RedirectResponse {
        $rejectInvite->execute($invite);
        $removeInviteActionsFromNotification->execute($invite);

        Notification::make()
            ->title('Convite recusado')
            ->body('O convite foi recusado.')
            ->warning()
            ->send();

        return redirect()
            ->to(TransactionResource::getUrl('index'));
    }
}

