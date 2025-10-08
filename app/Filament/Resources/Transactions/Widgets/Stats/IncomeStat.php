<?php

namespace App\Filament\Resources\Transactions\Widgets\Stats;

use App\Actions\GetAllIncomeTransactionsByStatementPeriod;
use App\Actions\GetAllUserMonthlyIncomeSources;
use App\Filament\Resources\Transactions\Widgets\Concerns\AggregatesTransactions;
use App\Models\IncomeSource;
use App\ValueObjects\StatementPeriod;
use Brick\Money\Money;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class IncomeStat
{
    use AggregatesTransactions;

    public function __construct(
        private GetAllIncomeTransactionsByStatementPeriod $getAllIncomeTransactionsByStatementPeriod,
        private GetAllUserMonthlyIncomeSources $getAllUserMonthlyIncomeSources
    ) {}

    public function make(StatementPeriod $statementPeriod): Stat
    {
        /** @var Money $incomes */
        $incomes = ($transactions = $this->getAllIncomeTransactionsByStatementPeriod
            ->execute($statementPeriod))
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        if (! $statementPeriod->isPast()) {
            $incomes = $incomes->plus($this->calculateProjectedIncome($statementPeriod));
        }

        $previousIncomes = $this->getAllIncomeTransactionsByStatementPeriod
            ->execute($statementPeriod->previous())
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        if ($statementPeriod->previous()->isPast()) {
            $previousIncomes = $previousIncomes->plus($this->calculateProjectedIncome($statementPeriod->previous()));
        }

        $formattedAmount = session()->get('hide_sensitive_data', false)
            ? '****'
            : $incomes->formatTo('pt_BR');

        return Stat::make('Entradas', $formattedAmount)
            ->icon(Heroicon::OutlinedArrowTrendingUp)
            ->color(Color::Green)
            ->description($this->description($incomes, $previousIncomes))
            ->chart($this->chart($transactions));
    }


    private function description(Money $incomes, Money $previousIncomes): string
    {
        if ($previousIncomes->isZero()) {
            return 'Sem dados do período anterior';
        }

        $difference = $incomes->minus($previousIncomes);
        $percentage = ($difference->getAmount()->toFloat() / $previousIncomes->getAmount()->toFloat()) * 100;

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

    private function calculateProjectedIncome(StatementPeriod $statementPeriod): Money
    {
        $incomeSourceIdsWithTransactions = $this->getAllIncomeTransactionsByStatementPeriod
            ->execute($statementPeriod)
            ->whereNotNull('income_source_id')
            ->pluck('income_source_id')
            ->unique();

        return $this->getAllUserMonthlyIncomeSources
            ->execute()
            ->reject(fn (IncomeSource $incomeSource) => $incomeSourceIdsWithTransactions->contains($incomeSource->id))
            ->reduce(function (Money $carry, IncomeSource $incomeSource) {
                return $carry->plus($incomeSource->average_amount);
            }, money()->of(0));
    }
}
