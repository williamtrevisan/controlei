<?php

namespace App\Filament\Resources\IncomeSources\Pages;

use App\Filament\Resources\IncomeSources\IncomeSourceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateIncomeSource extends CreateRecord
{
    protected static string $resource = IncomeSourceResource::class;

    public function getBreadcrumb(): string
    {
        return 'Nova fonte de renda';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Crie sua fonte de renda';
    }

    public function getSubheading(): ?string
    {
        return 'Adicione uma nova fonte de renda ao seu sistema de gestão.';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Fonte de renda criada com sucesso!';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        return $data;
    }
}
