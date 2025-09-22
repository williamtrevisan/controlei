<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AccountType: string implements HasColor, HasLabel
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Wallet = 'wallet';
    case Investment = 'investment';

    public function getColor(): string|array|null
    {
        return match ($this) {
            AccountType::Checking => Color::Green,
            AccountType::Savings => Color::Yellow,
            AccountType::Wallet => Color::Blue,
            AccountType::Investment => Color::Red,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            AccountType::Checking => 'Conta corrente',
            AccountType::Investment => 'Conta de investimentos',
            AccountType::Savings => 'Conta poupança',
            AccountType::Wallet => 'Conta digital',
        };
    }
}
