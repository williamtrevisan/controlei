<?php

namespace App\Filament\Resources\IncomeSources\Pages;

use App\Filament\Resources\IncomeSources\IncomeSourceResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListIncomeSources extends ListRecords
{
    protected static string $resource = IncomeSourceResource::class;

    protected $listeners = ['privacy-toggled' => '$refresh'];

    public function getBreadcrumb(): ?string
    {
        return 'Todas fontes de renda';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_sensitive_data')
                ->label('')
                ->tooltip(fn() => session()->get('hide_sensitive_data', false) ? 'Mostrar valores' : 'Ocultar valores')
                ->icon(fn() => session()->get('hide_sensitive_data', false) ? Heroicon::OutlinedEyeSlash : Heroicon::OutlinedEye)
                ->color('gray')
                ->action(function () {
                    $isHidden = ! session()->get('hide_sensitive_data', false);

                    session()->put('hide_sensitive_data', $isHidden);

                    $this->dispatch('privacy-toggled', hideData: $isHidden);
                }),

            CreateAction::make()
                ->label('Novo registro')
                ->icon(Heroicon::OutlinedPlus),
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
