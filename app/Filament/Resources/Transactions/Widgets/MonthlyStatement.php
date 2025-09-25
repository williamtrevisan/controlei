<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Widgets\Stats\BalanceStat;
use App\Filament\Resources\Transactions\Widgets\Stats\ExpenseStat;
use App\Filament\Resources\Transactions\Widgets\Stats\IncomeStat;
use App\ValueObjects\StatementPeriod;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;

class MonthlyStatement extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected $listeners = ['privacy-toggled' => '$refresh'];

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getStats(): array
    {
        $statementPeriod = $this->activeTab
            ? new StatementPeriod($this->activeTab)
            : (new StatementPeriod())->current();

        return [
            app()->make(IncomeStat::class)->make($statementPeriod),
            app()->make(ExpenseStat::class)->make($statementPeriod),
            app()->make(BalanceStat::class)->make($statementPeriod),
        ];
    }

    public function getColumns(): int|array|null
    {
        return ['default' => 1, 'md' => 3];
    }
}
