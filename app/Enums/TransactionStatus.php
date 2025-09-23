<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionStatus: string implements HasLabel, HasColor
{
    case Scheduled = 'scheduled';
    case Paid = 'paid';
    case Canceled = 'canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Scheduled => 'Agendado',
            self::Paid => 'Pago',
            self::Canceled => 'Cancelado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Scheduled => 'warning',
            self::Paid => 'success',
            self::Canceled => 'danger',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    public function isScheduled(): bool
    {
        return $this === self::Scheduled;
    }

    public function isCanceled(): bool
    {
        return $this === self::Canceled;
    }
}
