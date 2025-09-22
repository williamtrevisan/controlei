<?php

namespace App\DataTransferObjects;

use Banklink\Entities\Transaction;
use Banklink\Enums\TransactionDirection;
use Banklink\Enums\TransactionKind;
use Banklink\Enums\TransactionPaymentMethod;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TransactionData implements Arrayable
{
    public readonly string $id;

    public readonly string $hash;

    public readonly Carbon $createdAt;

    public readonly Carbon $updatedAt;

    public function __construct(
        public int $accountId,
        public ?int $cardId,
        public ?int $incomeSourceId,
        public Carbon $date,
        public string $description,
        public string $amount,
        public TransactionDirection $direction,
        public TransactionKind $kind,
        public TransactionPaymentMethod $paymentMethod,
        public ?int $currentInstallment,
        public ?int $totalInstallments,
        public ?string $statementPeriod,
    ) {
        $this->id = Str::uuid7()->toString();
        $this->hash = $this->hash();
        $this->createdAt = now();
        $this->updatedAt = now();
    }

    public static function from(
        Transaction $transaction,
        int $accountId,
        ?int $cardId = null,
        ?int $incomeSourceId = null,
        ?string $statementPeriod = null,
        ?TransactionKind $kind = null
    ): self {
        return new self(
            accountId: $accountId,
            cardId: $cardId,
            incomeSourceId: $incomeSourceId,
            date: $transaction->date(),
            description: $transaction->description(),
            amount: $transaction->amount()->getAmount(),
            direction: $transaction->direction(),
            kind: $kind ?? $transaction->kind(),
            paymentMethod: $transaction->paymentMethod(),
            currentInstallment: $transaction->installments()?->current(),
            totalInstallments: $transaction->installments()?->total(),
            statementPeriod: $statementPeriod,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->accountId,
            'card_id' => $this->cardId,
            'income_source_id' => $this->incomeSourceId,
            'date' => $this->date,
            'description' => $this->description,
            'amount' => $this->amount,
            'direction' => $this->direction,
            'kind' => $this->kind,
            'payment_method' => $this->paymentMethod,
            'current_installment' => $this->currentInstallment,
            'total_installments' => $this->totalInstallments,
            'statement_period' => $this->statementPeriod,
            'hash' => $this->hash,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    private function hash(): string
    {
        return hash('sha256', implode('|', [
            $this->accountId,
            $this->cardId,
            $this->date->format('Y-m-d'),
            $this->description,
            $this->amount,
            $this->currentInstallment,
        ]));
    }
}
