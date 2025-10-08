<?php

namespace App\Filament\Resources\Transactions\Widgets\Stats;

use App\Actions\GetAllUserExpenses;
use App\Actions\GetAllExpenseTransactionsByStatementPeriod;
use App\Filament\Resources\Transactions\Widgets\Concerns\AggregatesTransactions;
use App\Models\Expense;
use App\ValueObjects\StatementPeriod;
use Brick\Money\Money;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class ExpenseStat
{
    use AggregatesTransactions;

    public function __construct(
        private GetAllExpenseTransactionsByStatementPeriod $getAllExpenseTransactionsByStatementPeriod,
        private GetAllUserExpenses $getAllUserExpenses
    ) {}

    public function make(StatementPeriod $statementPeriod): Stat
    {
        /** @var Money $expenses */
        $expenses = ($transactions = $this->getAllExpenseTransactionsByStatementPeriod
            ->execute($statementPeriod))
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        if (! $statementPeriod->isPast()) {
            $expenses = $expenses->plus($this->calculateProjectedExpenses($statementPeriod));
        }

        $previousExpenses = $this->getAllExpenseTransactionsByStatementPeriod
            ->execute($statementPeriod->previous())
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        if ($statementPeriod->previous()->isPast()) {
            $previousExpenses = $previousExpenses->plus($this->calculateProjectedExpenses($statementPeriod->previous()));
        }

        $formattedAmount = session()->get('hide_sensitive_data', false)
            ? '****'
            : $expenses->formatTo('pt_BR');

        return Stat::make('Saídas', $formattedAmount)
            ->icon(Heroicon::OutlinedArrowTrendingDown)
            ->color(Color::Red)
            ->description($this->description($expenses, $previousExpenses, $statementPeriod))
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

        $formattedDifference = session()->get('hide_sensitive_data', false)
            ? '****'
            : $difference->formatTo('pt_BR');

        return sprintf('%+.1f%% (%s%s vs período anterior)', $percentage, $sign, $formattedDifference);
    }

    private function chart(Collection $transactions): Collection
    {
        if ($transactions->isEmpty()) {
            return collect()
                ->times(7, fn (): int => 0);
        }

        return $this->aggregateByDay($transactions)
            ->map(fn (Money $amount) => $amount->getMinorAmount()->toInt())
            ->sortKeys();
    }

    private function calculateProjectedExpenses(StatementPeriod $statementPeriod): Money
    {
        $expenseIdsWithTransactions = $this->getAllExpenseTransactionsByStatementPeriod
            ->execute($statementPeriod)
            ->whereNotNull('expense_id')
            ->pluck('expense_id')
            ->unique();

        return $this->getAllUserExpenses
            ->execute()
            ->reject(fn (Expense $expense) => $expenseIdsWithTransactions->contains($expense->id))
            ->reduce(function (Money $carry, Expense $expense) {
                $monthlyProjection = $expense->getMonthlyProjection();

                return $monthlyProjection
                    ? $carry->plus($monthlyProjection)
                    : $carry;
            }, money()->of(0));
    }
}
