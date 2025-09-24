<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    public function getBreadcrumb(): string
    {
        return 'Nova despesa';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Crie sua despesa';
    }

    public function getSubheading(): ?string
    {
        return 'Adicione uma nova despesa ao seu sistema de gestão.';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Despesa criada com sucesso!';
    }
}
