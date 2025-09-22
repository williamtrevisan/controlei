<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;

    public function getBreadcrumb(): string
    {
        return 'Nova conta';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Crie sua conta';
    }

    public function getSubheading(): ?string
    {
        return 'Adicione uma nova conta financeira ao seu sistema de gestão.';
    }
}
