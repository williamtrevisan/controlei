<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\TransactionDirection;
use App\Enums\TransactionKind;
use App\Enums\TransactionPaymentMethod;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Card;
use App\ValueObjects\StatementPeriod;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    public function getBreadcrumb(): string
    {
        return 'Novo gasto compartilhado';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Crie seu gasto compartilhado';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Registre uma compra feita no cartão de outra pessoa que você vai reembolsar.';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Gasto compartilhado criado com sucesso!';
    }
}
