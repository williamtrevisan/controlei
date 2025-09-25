<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    public function getBreadcrumb(): string
    {
        return 'Nova categoria';
    }

    public function getTitle(): string
    {
        return 'Nova categoria';
    }

    public function getSubheading(): ?string
    {
        return 'Crie uma nova categoria para organizar suas transações.';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Categoria criada com sucesso!';
    }
}
