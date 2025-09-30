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
        return 'Nova transação';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Criar nova transação';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Registre uma compra feita com seu cartão de crédito.';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Gasto compartilhado criado com sucesso!';
    }

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation(array_merge($data, [
            'account_id' => Card::query()->find($data['card_id'])->account->id,
            'direction' => TransactionDirection::Outflow,
            'kind' => TransactionKind::Purchase,
            'payment_method' => TransactionPaymentMethod::Credit,
            'statement_period' => (new StatementPeriod())->current()->rewind($data['current_installment'] - 1),
        ]));
    }
}
