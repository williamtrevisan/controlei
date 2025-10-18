<?php

namespace Tests\Support\Factories;

use App\DataTransferObjects\TransactionData;
use App\Enums\TransactionDirection;
use App\Enums\TransactionKind;
use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Support\Carbon;

class TransactionDataFactory extends Factory
{
    protected function make(array $attributes): TransactionData
    {
        return new TransactionData(
            accountId: $attributes['accountId'] ?? fake()->uuid(),
            cardId: $attributes['cardId'] ?? null,
            incomeSourceId: $attributes['incomeSourceId'] ?? null,
            expenseId: $attributes['expenseId'] ?? null,
            categoryId: $attributes['categoryId'] ?? null,
            statementId: $attributes['statementId'] ?? null,
            parentTransactionId: $attributes['parentTransactionId'] ?? null,
            date: $attributes['date'] ?? now(),
            description: $attributes['description'] ?? '::fake::',
            amount: $attributes['amount'] ?? 10000,
            direction: $attributes['direction'] ?? TransactionDirection::Outflow,
            kind: $attributes['kind'] ?? TransactionKind::Purchase,
            paymentMethod: $attributes['paymentMethod'] ?? TransactionPaymentMethod::Credit,
            currentInstallment: $attributes['currentInstallment'] ?? 1,
            totalInstallments: $attributes['totalInstallments'] ?? 1,
            status: $attributes['status'] ?? TransactionStatus::Scheduled,
            matcherRegex: $attributes['matcherRegex'] ?? null,
        );
    }
}

