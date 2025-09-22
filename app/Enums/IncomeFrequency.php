<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IncomeFrequency: string implements HasColor, HasLabel
{
    case Monthly = 'monthly';
    case Annually = 'annually';
    case Occasionally = 'occasionally';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Monthly => 'success',
            self::Annually => 'warning',
            self::Occasionally => 'gray',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Monthly => 'Receita recorrente mensal (salário, freelance)',
            self::Annually => 'Receita anual (13º salário, bonificações)',
            self::Occasionally => 'Receita eventual (FGTS, restituição IR)',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Monthly => 'Mensal',
            self::Annually => 'Anual',
            self::Occasionally => 'Eventual',
        };
    }

    public function isMonthly(): bool
    {
        return $this === IncomeFrequency::Monthly;
    }
}
