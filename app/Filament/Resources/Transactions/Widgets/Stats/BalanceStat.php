<?php

namespace App\Filament\Resources\Transactions\Widgets\Stats;

use App\Actions\GetAllExpenses;
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
        private GetAllIncomeTransactionsByStatementPeriod $getAllIncomeTransactionsByStatementPeriod,
        private GetProjectedMonthlyIncome $getProjectedIncome,
        private GetAllExpenseTransactionsByStatementPeriod $getAllExpenseTransactionsByStatementPeriod,
        private GetAllExpenses $getAllExpenses
    ) {}

    public function make(StatementPeriod $statementPeriod): Stat
    {
        $this->statementPeriod = $statementPeriod;

        /** @var Money $incomes */
        $incomes = ($incomeTransactions = $this->getAllIncomeTransactionsByStatementPeriod
            ->execute($statementPeriod))
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));
        if ($incomes->isZero()) {
            $incomes = $this->getProjectedIncome->execute();
        }

        /** @var Money $expenses */
        $expenses = ($expenseTransactions = $this->getAllExpenseTransactionsByStatementPeriod
            ->execute($statementPeriod))
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        if ($statementPeriod->isFuture()) {
            $expenses = $expenses->plus($this->calculateProjectedExpenses($statementPeriod));
        }

        $balance = $incomes->minus($expenses);

        $formattedAmount = session()->get('hide_sensitive_data', false) 
            ? '****'
            : $balance->formatTo('pt_BR');

        return Stat::make('Saldo', $formattedAmount)
            ->icon($this->icon($balance))
            ->color($this->color($balance))
            ->description($this->description($balance, $statementPeriod))
            ->chart($this->chart($incomeTransactions, $expenseTransactions));
    }

    private function description(Money $balance, StatementPeriod $statementPeriod): string
    {
        if (($previousBalance = $this->previousBalance())->isZero()) {
            return 'Sem dados do período anterior';
        }

        $difference = $balance->minus($previousBalance);
        $percentage = ($difference->getAmount()->toFloat() / abs($previousBalance->getAmount()->toFloat())) * 100;

        $sign = $difference->isPositiveOrZero() ? '+' : '';
        
        $formattedDifference = session()->get('hide_sensitive_data', false)
            ? '****'
            : $difference->formatTo('pt_BR');

        return sprintf('%+.1f%% (%s%s vs período anterior)', $percentage, $sign, $formattedDifference);
    }

    private function previousBalance(): Money
    {
        $previousIncomes = $this->getAllIncomeTransactionsByStatementPeriod
            ->execute($this->statementPeriod->previous())
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        if ($previousIncomes->isZero()) {
            $previousIncomes = $this->getProjectedIncome->execute();
        }

        $previousExpenses = $this->getAllExpenseTransactionsByStatementPeriod
            ->execute($this->statementPeriod->previous())
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        if ($this->statementPeriod->previous()->isFuture()) {
            $previousExpenses = $previousExpenses->plus($this->calculateProjectedExpenses($this->statementPeriod->previous()));
        }

        return $previousIncomes->minus($previousExpenses);
    }

    private function calculateProjectedExpenses(StatementPeriod $statementPeriod): Money
    {
        $expenseIdsWithTransactions = $this->getAllExpenseTransactionsByStatementPeriod
            ->execute($statementPeriod)
            ->whereNotNull('expense_id')
            ->pluck('expense_id')
            ->unique();

        return $this->getAllExpenses->execute()
            ->reject(fn ($expense) => $expenseIdsWithTransactions->contains($expense->id))
            ->reduce(function (Money $carry, $expense) {
                $monthlyProjection = $expense->getMonthlyProjection();

                return $monthlyProjection
                    ? $carry->plus($monthlyProjection)
                    : $carry;
            }, money()->of(0));
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
