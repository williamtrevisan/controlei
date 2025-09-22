<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum IncomeSourceType: string implements HasColor, HasLabel
{
    case Freelance = 'freelance';
    case Other = 'other';
    case Salary = 'salary';

    public function getColor(): string|array|null
    {
        return match ($this) {
            IncomeSourceType::Freelance => Color::Green,
            IncomeSourceType::Other => Color::Yellow,
            IncomeSourceType::Salary => Color::Blue,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            IncomeSourceType::Freelance => 'Freelance',
            IncomeSourceType::Other => 'Outro',
            IncomeSourceType::Salary => 'Salário',
        };
    }
}
