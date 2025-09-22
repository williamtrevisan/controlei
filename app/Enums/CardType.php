<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CardType: string implements HasColor, HasLabel
{
    case Credit = 'credit';
    case Debit = 'debit';

    public function getColor(): string|array|null
    {
        return match ($this) {
            CardType::Credit => Color::Green,
            CardType::Debit => Color::Red,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            CardType::Credit => 'Crédito',
            CardType::Debit => 'Débito',
        };
    }
}
