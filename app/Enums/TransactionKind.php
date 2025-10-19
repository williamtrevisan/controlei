<?php

namespace App\Enums;

use App\Models\Transaction;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TransactionKind: string implements HasColor, HasLabel
{
    case Cashback = 'cashback';
    case Fee = 'fee';
    case InvoicePayment = 'invoice_payment';
    case Purchase = 'purchase';
    case Refund = 'refund';

    public static function fromTransaction(Transaction $transaction): self
    {
        return match (true) {
            $transaction->isCashback() => TransactionKind::Cashback,
            $transaction->isFee() => TransactionKind::Fee,
            $transaction->isRefund() => TransactionKind::Refund,
            $transaction->isInvoicePayment() => TransactionKind::InvoicePayment,
            default => TransactionKind::Purchase,
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            TransactionKind::Cashback => Color::Emerald,
            TransactionKind::Fee => Color::Rose,
            TransactionKind::InvoicePayment => Color::Amber,
            TransactionKind::Purchase => Color::Indigo,
            TransactionKind::Refund => Color::Teal,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            TransactionKind::Cashback => 'Cashback',
            TransactionKind::Fee => 'Taxa',
            TransactionKind::InvoicePayment => 'Pagamento da fatura',
            TransactionKind::Purchase => 'Compra',
            TransactionKind::Refund => 'Estorno',
        };
    }

    public function isInvoicePayment(): bool
    {
        return $this === TransactionKind::InvoicePayment;
    }
}
