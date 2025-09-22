<?php

namespace App\Filament\Resources\IncomeSources\Pages;

use App\Filament\Resources\IncomeSources\IncomeSourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListIncomeSources extends ListRecords
{
    protected static string $resource = IncomeSourceResource::class;

    public function getBreadcrumb(): ?string
    {
        return 'Todas fontes de renda';
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
        return 'Gerencie suas fontes de renda e acompanhe seus ganhos.';
    }

    public function getTitle(): string
    {
        return 'Minhas fontes de renda';
    }
}
