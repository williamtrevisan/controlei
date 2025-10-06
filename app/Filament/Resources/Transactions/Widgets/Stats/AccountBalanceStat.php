<?php

namespace App\Filament\Resources\Transactions\Widgets\Stats;

use App\Models\Account;
use Brick\Money\Money;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountBalanceStat extends StatsOverviewWidget
{
    protected $listeners = ['privacy-toggled' => '$refresh'];

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $balance = Account::query()->first()->balance;

        $formattedBalance = session()->get('hide_sensitive_data', false)
            ? '****'
            : $balance->formatTo('pt_BR');

        return [
            Stat::make('Saldo atual', $formattedBalance)
                ->description('Saldo disponível na sua conta bancária')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color($this->color($balance)),
        ];
    }

    private function color(Money $balance): array
    {
        if ($balance->isPositive()) {
            return Color::Green;
        }

        if ($balance->isNegative()) {
            return Color::Red;
        }

        return Color::Gray;
    }

    public function getColumns(): int|array|null
    {
        return 1;
    }
}

