<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ExpenseFrequency: string implements HasColor, HasLabel
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annually = 'annually';
    case Occasionally = 'occasionally';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Monthly => 'danger',
            self::Quarterly => 'warning',
            self::Annually => 'info',
            self::Occasionally => 'gray',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Monthly => 'Gasto recorrente mensal (aluguel, financiamento)',
            self::Quarterly => 'Gasto trimestral (IPTU, IPVA)',
            self::Annually => 'Gasto anual (seguro, renovações)',
            self::Occasionally => 'Gasto eventual (viagens, emergências)',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Monthly => 'Mensal',
            self::Quarterly => 'Trimestral',
            self::Annually => 'Anual',
            self::Occasionally => 'Eventual',
        };
    }

    public function isMonthly(): bool
    {
        return $this === self::Monthly;
    }

    public function isRecurring(): bool
    {
        return in_array($this, [self::Monthly, self::Quarterly, self::Annually]);
    }
}
