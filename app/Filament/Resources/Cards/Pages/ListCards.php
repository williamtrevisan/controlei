<?php

namespace App\Filament\Resources\Cards\Pages;

use App\Filament\Resources\Cards\CardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCards extends ListRecords
{
    protected static string $resource = CardResource::class;

    public function getBreadcrumb(): ?string
    {
        return 'Todos cartões';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo registro')
                ->icon(Heroicon::Plus),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Gerencie seus cartões de crédito e débito.';
    }

    public function getTitle(): string
    {
        return 'Meus cartões';
    }
}
