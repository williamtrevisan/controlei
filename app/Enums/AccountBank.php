<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AccountBank: string implements HasLabel
{
    case Itau = 'itau';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            AccountBank::Itau => 'Itaú',
        };
    }
}
