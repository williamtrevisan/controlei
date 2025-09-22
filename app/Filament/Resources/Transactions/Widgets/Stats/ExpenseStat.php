<?php

namespace App\Filament\Resources\Transactions\Widgets\Stats;

use App\Actions\GetAllExpenseTransactionsByStatementPeriod;
use App\Filament\Resources\Transactions\Widgets\Concerns\AggregatesTransactions;
use App\Models\Transaction;
use App\ValueObjects\StatementPeriod;
use Brick\Money\Money;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExpenseStat
{
    use AggregatesTransactions;

    public function __construct(
        private GetAllExpenseTransactionsByStatementPeriod $getExpenseTransactions
    ) {}

    public function make(StatementPeriod $statementPeriod): Stat
    {
        /** @var Money $expenses */
        $expenses = ($transactions = $this->getExpenseTransactions
            ->execute($statementPeriod))
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        $previousExpenses = $this->getExpenseTransactions
            ->execute($statementPeriod->previous())
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        return Stat::make('Saídas', $expenses->formatTo('pt_BR'))
            ->icon(Heroicon::OutlinedArrowTrendingDown)
            ->color(Color::Red)
            ->description($this->description($expenses, $previousExpenses))
            ->chart($this->chart($transactions));
    }

    private function description(Money $expenses, Money $previousExpenses): string
    {
        if ($previousExpenses->isZero()) {
            return 'Sem dados do período anterior';
        }

        $difference = $expenses->minus($previousExpenses);
        $percentage = ($difference->getAmount()->toFloat() / $previousExpenses->getAmount()->toFloat()) * 100;

        $sign = $difference->isPositiveOrZero() ? '+' : '';
        return sprintf('%+.1f%% (%s%s vs período anterior)', $percentage, $sign, $difference->formatTo('pt_BR'));
    }

    private function chart(Collection $transactions): Collection
    {
        return $this->aggregateByDay($transactions)
            ->map(fn (Money $amount) => $amount->getMinorAmount()->toInt())
            ->sortKeys();
    }
}
