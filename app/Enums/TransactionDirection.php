<?php

namespace App\Enums;

enum TransactionDirection: string
{
    case Inflow = 'inflow';
    case Outflow = 'outflow';

    public function isInflow(): bool
    {
        return $this === TransactionDirection::Inflow;
    }

    public function isOutflow(): bool
    {
        return $this === TransactionDirection::Outflow;
    }
}
