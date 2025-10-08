<?php

namespace Tests\Support\Factories;

use App\DataTransferObjects\StatementData;
use App\Enums\StatementStatus;
use Illuminate\Support\Carbon;

class StatementDataFactory extends Factory
{
    protected function make(array $attributes): StatementData
    {
        $period = $attributes['period'] ?? now()->format('Y-m');
        [$year, $month] = explode('-', $period);

        return new StatementData(
            accountId: $attributes['accountId'] ?? fake()->uuid(),
            cardId: $attributes['cardId'] ?? null,
            parentStatementId: $attributes['parentStatementId'] ?? null,
            period: $period,
            closingDate: $attributes['closingDate'] ?? Carbon::create($year, $month)->endOfMonth(),
            dueDate: $attributes['dueDate'] ?? Carbon::create($year, $month)->endOfMonth(),
            status: $attributes['status'] ?? StatementStatus::Open,
            amount: $attributes['amount'] ?? 0,
        );
    }
}

