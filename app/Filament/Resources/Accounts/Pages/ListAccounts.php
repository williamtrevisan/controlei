<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    public function getBreadcrumb(): ?string
    {
        return 'Todas contas';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo registro')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Gerencie suas contas bancárias, cartões de crédito e outras contas financeiras.';
    }

    public function getTitle(): string
    {
        return 'Minhas contas';
    }
}
