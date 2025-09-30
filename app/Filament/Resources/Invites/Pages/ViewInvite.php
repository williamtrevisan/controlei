<?php

namespace App\Filament\Resources\Invites\Pages;

use App\Filament\Resources\Invites\InviteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvite extends ViewRecord
{
    protected static string $resource = InviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
