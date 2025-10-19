<?php

namespace App\Filament\Resources\Accounts\Widgets;

use App\Actions\GetUserAccount;
use Brick\Money\Money;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Arr;

class AccountBalanceStat extends StatsOverviewWidget
{
    protected $listeners = ['privacy-toggled' => '$refresh'];

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $balance = app()->make(GetUserAccount::class)->execute()?->balance ?? money()->of(0);

        $formattedBalance = session()->get('hide_sensitive_data', false)
            ? '****'
            : $balance->formatTo('pt_BR');

        $color = $this->color($balance);

        return [
            Stat::make('Saldo da sua conta bancária', $formattedBalance)
                ->extraAttributes([
                    'class' => 'fi-sidebar-account-balance',
                    'style' => <<<CSS
                        background-color: transparent;
                        padding: 0.435rem 0.875rem;
                        border-left: 3px solid $color;
                        transition: all 0.2s ease;
                        overflow: hidden;
                        cursor: help;
                    CSS,
                ]),
        ];
    }

    private function color(Money $balance): string
    {
        if ($balance->isPositive()) {
            return Arr::get(Color::Green, '600');
        }

        if ($balance->isNegative()) {
            return Arr::get(Color::Red, '600');
        }

        return Arr::get(Color::Gray, '600');
    }

    public function getColumns(): int|array|null
    {
        return 1;
    }
}

