<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CardBrand: string implements HasColor, HasLabel
{
    case Visa = 'visa';
    case Mastercard = 'mastercard';

    public function getColor(): string|array|null
    {
        return match ($this) {
            CardBrand::Visa => Color::Blue,
            CardBrand::Mastercard => Color::Red,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            CardBrand::Visa => 'Visa',
            CardBrand::Mastercard => 'Mastercard',
        };
    }
}
