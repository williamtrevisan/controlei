<?php

namespace App\Filament\Resources\Cards\Pages;

use App\Filament\Resources\Cards\CardResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateCard extends CreateRecord
{
    protected static string $resource = CardResource::class;

    public function getBreadcrumb(): string
    {
        return 'Novo cartão';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Crie seu cartão';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Adicione um novo cartão ao seu sistema de gestão';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Cartão criado com sucesso!';
    }
}
