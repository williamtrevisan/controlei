<?php

namespace App\Actions;

use App\Enums\IncomeFrequency;
use App\Models\IncomeSource;
use Brick\Money\Money;

class GetProjectedMonthlyIncome
{
    public function execute(): Money
    {
        return IncomeSource::query()
            ->where('active', true)
            ->where('frequency', IncomeFrequency::Monthly->value)
            ->whereNotNull('average_amount')
            ->get()
            ->reduce(
                fn (Money $carry, IncomeSource $incomeSource) => $carry->plus($incomeSource->average_amount),
                money()->of(0),
            );
    }
}
