<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InvitationStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Blocked = 'blocked';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Accepted => 'Aceito',
            self::Rejected => 'Rejeitado',
            self::Blocked => 'Bloqueado',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isAccepted(): bool
    {
        return $this === self::Accepted;
    }
}
