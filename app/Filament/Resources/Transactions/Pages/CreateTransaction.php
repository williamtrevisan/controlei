<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Actions\GetOrCreateStatementForTransaction;
use App\Enums\TransactionDirection;
use App\Enums\TransactionKind;
use App\Enums\TransactionPaymentMethod;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Card;
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
        $card = Card::query()->find($data['card_id']);
        $statement = app()->make(GetOrCreateStatementForTransaction::class)
            ->execute($card, now());

        return parent::handleRecordCreation(array_merge($data, [
            'account_id' => $card->account->id,
            'statement_id' => $statement->id,
            'direction' => TransactionDirection::Outflow,
            'kind' => TransactionKind::Purchase,
            'payment_method' => TransactionPaymentMethod::Credit,
        ]));
    }
}
