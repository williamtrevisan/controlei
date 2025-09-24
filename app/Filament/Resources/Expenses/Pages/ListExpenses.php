<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    public function getBreadcrumb(): ?string
    {
        return 'Todas despesas';
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
        return 'Gerencie suas despesas e acompanhe seus gastos.';
    }

    public function getTitle(): string
    {
        return 'Minhas despesas';
    }
}
