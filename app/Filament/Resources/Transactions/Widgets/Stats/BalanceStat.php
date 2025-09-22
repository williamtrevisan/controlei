<?php

namespace App\Filament\Resources\Transactions\Widgets\Stats;

use App\Actions\GetAllExpenseTransactionsByStatementPeriod;
use App\Actions\GetAllIncomeTransactionsByStatementPeriod;
use App\Actions\GetProjectedMonthlyIncome;
use App\Filament\Resources\Transactions\Widgets\Concerns\AggregatesTransactions;
use App\ValueObjects\StatementPeriod;
use Brick\Money\Money;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class BalanceStat
{
    use AggregatesTransactions;

    private StatementPeriod $statementPeriod;

    public function __construct(
        private GetAllIncomeTransactionsByStatementPeriod $getIncomeTransactions,
        private GetProjectedMonthlyIncome $getProjectedIncome,
        private GetAllExpenseTransactionsByStatementPeriod $getExpenseTransactions
    ) {}

    public function make(StatementPeriod $statementPeriod): Stat
    {
        $this->statementPeriod = $statementPeriod;

        /** @var Money $incomes */
        $incomes = ($incomeTransactions = $this->getIncomeTransactions
            ->execute($statementPeriod))
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));
        if ($incomes->isZero()) {
            $incomes = $this->getProjectedIncome->execute();
        }

        /** @var Money $expenses */
        $expenses = ($expenseTransactions = $this->getExpenseTransactions
            ->execute($statementPeriod))
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        $balance = $incomes->minus($expenses);

        return Stat::make('Saldo', $balance->formatTo('pt_BR'))
            ->icon($this->icon($balance))
            ->color($this->color($balance))
            ->description($this->description($balance))
            ->chart($this->chart($incomeTransactions, $expenseTransactions));
    }

    private function description(Money $balance): string
    {
        $previousBalance = $this->previousBalance();

        if ($previousBalance->isZero()) {
            return 'Sem dados do período anterior';
        }

        $difference = $balance->minus($previousBalance);
        $percentage = ($difference->getAmount()->toFloat() / abs($previousBalance->getAmount()->toFloat())) * 100;

        $sign = $difference->isPositiveOrZero() ? '+' : '';
        return sprintf('%+.1f%% (%s%s vs período anterior)', $percentage, $sign, $difference->formatTo('pt_BR'));
    }

    private function previousBalance(): Money
    {
        $previousIncomes = $this->getIncomeTransactions
            ->execute($this->statementPeriod->previous())
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        if ($previousIncomes->isZero()) {
            $previousIncomes = $this->getProjectedIncome->execute();
        }

        $previousExpenses = $this->getExpenseTransactions
            ->execute($this->statementPeriod->previous())
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        return $previousIncomes->minus($previousExpenses);
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

    private function icon(Money $balance): Heroicon
    {
        if ($balance->isPositive()) {
            return Heroicon::OutlinedArrowTrendingUp;
        }

        if ($balance->isNegative()) {
            return Heroicon::OutlinedArrowTrendingDown;
        }

        return Heroicon::OutlinedBanknotes;
    }

    private function chart(Collection $incomes, Collection $expenses): Collection
    {
        $incomesByDay = $this->aggregateByDay($incomes);
        $expensesByDay = $this->aggregateByDay($expenses);

        $dates = $incomes->concat($expenses)
            ->pluck('date')
            ->map->format('Y-m-d')
            ->unique()
            ->sort();

        return $dates
            ->mapWithKeys(fn (string $date) => [
                $date => $incomesByDay->get($date, money()->of(0))
                    ->minus($expensesByDay->get($date, money()->of(0)))
                    ->getMinorAmount()
                    ->toInt()
            ]);
    }
}
