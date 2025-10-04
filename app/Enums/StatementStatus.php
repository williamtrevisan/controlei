<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum StatementStatus: string implements HasColor, HasLabel, HasIcon
{
    case Open = 'open';
    case Closed = 'closed';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function getColor(): string|array|null
    {
        return match ($this) {
            StatementStatus::Open => 'success',
            StatementStatus::Closed => 'warning',
            StatementStatus::Paid => 'gray',
            StatementStatus::Overdue => 'danger',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            StatementStatus::Open => 'Fatura em aberto, ainda recebendo transações',
            StatementStatus::Closed => 'Fatura fechada, aguardando pagamento',
            StatementStatus::Paid => 'Fatura paga',
            StatementStatus::Overdue => 'Fatura vencida, pagamento em atraso',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            StatementStatus::Open => 'heroicon-o-lock-open',
            StatementStatus::Closed => 'heroicon-o-lock-closed',
            StatementStatus::Paid => 'heroicon-o-check-circle',
            StatementStatus::Overdue => 'heroicon-o-exclamation-triangle',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            StatementStatus::Open => 'Aberta',
            StatementStatus::Closed => 'Fechada',
            StatementStatus::Paid => 'Paga',
            StatementStatus::Overdue => 'Vencida',
        };
    }

    public function isOpen(): bool
    {
        return $this === StatementStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this === StatementStatus::Closed;
    }

    public function isPaid(): bool
    {
        return $this === StatementStatus::Paid;
    }

    public function isOverdue(): bool
    {
        return $this === StatementStatus::Overdue;
    }
}
