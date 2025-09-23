<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CardOwner: string implements HasLabel, HasColor
{
    case Dad = 'dad';
    case Mom = 'mom';
    case Brother = 'brother';
    case Sister = 'sister';
    case Partner = 'partner';
    case Friend = 'friend';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dad => 'Pai',
            self::Mom => 'Mãe',
            self::Brother => 'Irmão',
            self::Sister => 'Irmã',
            self::Partner => 'Parceiro(a)',
            self::Friend => 'Amigo(a)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Dad => 'blue',
            self::Mom => 'pink',
            self::Brother => 'indigo',
            self::Sister => 'purple',
            self::Partner => 'red',
            self::Friend => 'gray',
        };
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::Dad => 'do Pai',
            self::Mom => 'da Mãe',
            self::Brother => 'do Irmão',
            self::Sister => 'da Irmã',
            self::Partner => 'do(a) Parceiro(a)',
            self::Friend => 'do(a) Amigo(a)',
        };
    }
}
