<?php

namespace App\Enums;

use App\Models\Card;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TransactionPaymentMethod: string implements HasColor, HasLabel
{
    case Cash = 'cash';
    case Credit = 'credit';
    case Debit = 'debit';
    case Doc = 'doc';
    case Pix = 'pix';
    case Ted = 'ted';

    public static function fromTransactionDescription(string $description): self
    {
        if (str($description)->startsWith('PIX')) {
            return TransactionPaymentMethod::Pix;
        }

        if (str($description)->startsWith('TED')) {
            return TransactionPaymentMethod::Cash;
        }

        $card = Card::query()
            ->get(['id', 'matcher_regex'])
            ->first(
                fn (Card $card): bool
                => str($description)->isMatch($card->matcher_regex)
            );
        if ($card || str($description)->startsWith('PAG BOLETO')) {
            return TransactionPaymentMethod::Debit;
        }

        return TransactionPaymentMethod::Credit;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            TransactionPaymentMethod::Cash => Color::Amber,
            TransactionPaymentMethod::Credit => Color::Indigo,
            TransactionPaymentMethod::Debit => Color::Rose,
            TransactionPaymentMethod::Doc => Color::Cyan,
            TransactionPaymentMethod::Pix => Color::Emerald,
            TransactionPaymentMethod::Ted => Color::Violet,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            TransactionPaymentMethod::Cash => 'Dinheiro',
            TransactionPaymentMethod::Credit => 'Crédito',
            TransactionPaymentMethod::Debit => 'Débito',
            TransactionPaymentMethod::Doc => 'DOC',
            TransactionPaymentMethod::Pix => 'Pix',
            TransactionPaymentMethod::Ted => 'TED',
        };
    }

    public function isPix(): bool
    {
        return $this === TransactionPaymentMethod::Pix;
    }
}
