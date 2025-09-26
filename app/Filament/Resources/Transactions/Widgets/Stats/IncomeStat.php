<?php

namespace App\Filament\Resources\Transactions\Widgets\Stats;

use App\Actions\GetAllIncomeTransactionsByStatementPeriod;
use App\Actions\GetProjectedMonthlyIncome;
use App\Filament\Resources\Transactions\Widgets\Concerns\AggregatesTransactions;
use App\ValueObjects\StatementPeriod;
use Brick\Money\Money;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class IncomeStat
{
    use AggregatesTransactions;

    private bool $isProjection = false;

    public function __construct(
        private GetAllIncomeTransactionsByStatementPeriod $getAllIncomeTransactionsByStatementPeriod,
        private GetProjectedMonthlyIncome $getProjectedIncome
    ) {}

    public function make(StatementPeriod $statementPeriod): Stat
    {
        /** @var Money $incomes */
        $incomes = ($transactions = $this->getAllIncomeTransactionsByStatementPeriod
            ->execute($statementPeriod))
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));
        if ($incomes->isZero()) {
            $this->isProjection = true;
            $incomes = $this->getProjectedIncome->execute();
        }

        $previousIncomes = $this->getAllIncomeTransactionsByStatementPeriod
            ->execute($statementPeriod->previous())
            ->reduce(fn ($carry, $transaction) => $carry->plus($transaction->amount), money()->of(0));

        $formattedAmount = session()->get('hide_sensitive_data', false) 
            ? '****'
            : $incomes->formatTo('pt_BR');

        return Stat::make('Entradas', $formattedAmount)
            ->icon(Heroicon::OutlinedArrowTrendingUp)
            ->color($this->isProjection ? Color::Blue : Color::Green)
            ->description($this->description($incomes, $previousIncomes))
            ->chart($this->chart($transactions));
    }


    private function description(Money $incomes, Money $previousIncomes): string
    {
        if ($this->isProjection) {
            return 'Estimativa mensal';
        }

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
        if ($this->isProjection) {
            return collect()
                ->times(2, fn (): int => 1);
        }

        return $this->aggregateByDay($transactions)
            ->map(fn (Money $amount) => $amount->getMinorAmount()->toInt())
            ->sortKeys();
    }
}
