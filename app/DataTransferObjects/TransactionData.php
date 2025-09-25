<?php

namespace App\DataTransferObjects;

use App\Enums\TransactionDirection;
use App\Enums\TransactionKind;
use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\ValueObjects\StatementPeriod;
use Banklink\Entities;
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
        public ?int $expenseId,
        public ?int $categoryId = null,
        public ?string $parentTransactionId,
        public Carbon $date,
        public string $description,
        public int $amount,
        public TransactionDirection|\Banklink\Enums\TransactionDirection $direction,
        public TransactionKind|\Banklink\Enums\TransactionKind $kind,
        public TransactionPaymentMethod|\Banklink\Enums\TransactionPaymentMethod $paymentMethod,
        public ?int $currentInstallment,
        public ?int $totalInstallments,
        public TransactionStatus $status = TransactionStatus::Paid,
        public ?string $matcherRegex,
        public ?string $statementPeriod,
    ) {
        $this->id = Str::uuid7()->toString();
        $this->hash = $this->hash();
        $this->createdAt = now();
        $this->updatedAt = now();
    }

    public static function from(
        Entities\Transaction $transaction,
        int $accountId,
        ?int $cardId = null,
        ?int $incomeSourceId = null,
        ?int $expenseId = null,
        ?string $statementPeriod = null
    ): self {
        return new self(
            accountId: $accountId,
            cardId: $cardId,
            incomeSourceId: $incomeSourceId,
            expenseId: $expenseId,
            categoryId: null,
            parentTransactionId: null,
            date: $transaction->date(),
            description: $transaction->description(),
            amount: $transaction->amount()->getMinorAmount()->toInt(),
            direction: $transaction->direction(),
            kind: $transaction->kind(),
            paymentMethod: $transaction->paymentMethod(),
            currentInstallment: $transaction->installments()?->current(),
            totalInstallments: $transaction->installments()?->total(),
            status: TransactionStatus::Paid,
            matcherRegex: null,
            statementPeriod: $statementPeriod,
        );
    }

    public static function fromEntity(
        Transaction $transaction,
        int $installment,
        StatementPeriod $statementPeriod
    ): self {
        return new self(
            accountId: $transaction->account?->id,
            cardId: $transaction->card?->id,
            incomeSourceId: null,
            expenseId: null,
            categoryId: $transaction->category?->id,
            parentTransactionId: $transaction->id,
            date: $transaction->date,
            description: $transaction->description,
            amount: $transaction->amount->getMinorAmount()->toInt(),
            direction: $transaction->direction,
            kind: $transaction->kind,
            paymentMethod: $transaction->payment_method,
            currentInstallment: $installment,
            totalInstallments: $transaction->total_installments,
            status: $installment > $transaction->getRelation('lastPaidInstallment') ? TransactionStatus::Scheduled : TransactionStatus::Paid,
            matcherRegex: null,
            statementPeriod: $statementPeriod->value(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->accountId,
            'card_id' => $this->cardId,
            'income_source_id' => $this->incomeSourceId,
            'expense_id' => $this->expenseId,
            'category_id' => $this->categoryId,
            'parent_transaction_id' => $this->parentTransactionId,
            'date' => $this->date,
            'description' => $this->description,
            'amount' => $this->amount,
            'direction' => $this->direction,
            'kind' => $this->kind,
            'payment_method' => $this->paymentMethod,
            'current_installment' => $this->currentInstallment,
            'total_installments' => $this->totalInstallments,
            'statement_period' => $this->statementPeriod,
            'status' => $this->status,
            'matcher_regex' => $this->matcherRegex,
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
