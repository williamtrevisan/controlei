<?php

namespace App\DataTransferObjects;

use App\Enums\TransactionDirection;
use App\Enums\TransactionKind;
use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Statement;
use App\Models\Transaction;
use Banklink\Entities;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

readonly class TransactionData implements Arrayable
{
    public string $id;

    public string $hash;

    public Carbon $createdAt;

    public Carbon $updatedAt;

    public function __construct(
        public string $accountId,
        public ?string $cardId,
        public ?string $incomeSourceId,
        public ?string $expenseId,
        public ?int $categoryId = null,
        public ?string $statementId,
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
    ) {
        $this->id = Str::uuid7()->toString();
        $this->hash = $this->hash();
        $this->createdAt = now();
        $this->updatedAt = now();
    }

    public static function from(
        Entities\Transaction $transaction,
        string $accountId,
        ?string $cardId = null,
        ?string $statementId = null,
    ): self {
        return new self(
            accountId: $accountId,
            cardId: $cardId,
            incomeSourceId: null,
            expenseId: null,
            categoryId: 8,
            statementId: $statementId,
            parentTransactionId: null,
            date: $transaction->date(),
            description: $transaction->description(),
            amount: $transaction->amount()->getMinorAmount()->toInt(),
            direction: $transaction->direction(),
            kind: $transaction->kind(),
            paymentMethod: $transaction->paymentMethod(),
            currentInstallment: $transaction->installments()?->current(),
            totalInstallments: $transaction->installments()?->total(),
            status: $transaction->statementPeriod()->isCurrentOrPast()
                ? TransactionStatus::Paid
                : TransactionStatus::Scheduled,
            matcherRegex: null,
        );
    }

    public static function fromEntity(
        Transaction $transaction,
        int $installment,
        Statement $statement
    ): self {
        return new self(
            accountId: $transaction->account?->id,
            cardId: $transaction->card?->id,
            incomeSourceId: null,
            expenseId: null,
            categoryId: $transaction->category?->id,
            statementId: $statement->id,
            parentTransactionId: $transaction->id,
            date: $transaction->date,
            description: $transaction->description,
            amount: $transaction->amount->getMinorAmount()->toInt(),
            direction: $transaction->direction,
            kind: $transaction->kind,
            paymentMethod: $transaction->payment_method,
            currentInstallment: $installment,
            totalInstallments: $transaction->total_installments,
            status: $statement->period->isPast() ? TransactionStatus::Paid : TransactionStatus::Scheduled,
            matcherRegex: null,
        );
    }

    public function withStatementId(string $statementId): self
    {
        return new self(
            accountId: $this->accountId,
            cardId: $this->cardId,
            incomeSourceId: $this->incomeSourceId,
            expenseId: $this->expenseId,
            categoryId: $this->categoryId,
            statementId: $statementId,
            parentTransactionId: $this->parentTransactionId,
            date: $this->date,
            description: $this->description,
            amount: $this->amount,
            direction: $this->direction,
            kind: $this->kind,
            paymentMethod: $this->paymentMethod,
            currentInstallment: $this->currentInstallment,
            totalInstallments: $this->totalInstallments,
            status: $this->status,
            matcherRegex: $this->matcherRegex,
        );
    }

    public function withExpenseId(string $expenseId): self
    {
        return new self(
            accountId: $this->accountId,
            cardId: $this->cardId,
            incomeSourceId: $this->incomeSourceId,
            expenseId: $expenseId,
            categoryId: $this->categoryId,
            statementId: $this->statementId,
            parentTransactionId: $this->parentTransactionId,
            date: $this->date,
            description: $this->description,
            amount: $this->amount,
            direction: $this->direction,
            kind: $this->kind,
            paymentMethod: $this->paymentMethod,
            currentInstallment: $this->currentInstallment,
            totalInstallments: $this->totalInstallments,
            status: $this->status,
            matcherRegex: $this->matcherRegex,
        );
    }

    public function withIncomeSourceId(string $incomeSourceId): self
    {
        return new self(
            accountId: $this->accountId,
            cardId: $this->cardId,
            incomeSourceId: $incomeSourceId,
            expenseId: $this->expenseId,
            categoryId: $this->categoryId,
            statementId: $this->statementId,
            parentTransactionId: $this->parentTransactionId,
            date: $this->date,
            description: $this->description,
            amount: $this->amount,
            direction: $this->direction,
            kind: $this->kind,
            paymentMethod: $this->paymentMethod,
            currentInstallment: $this->currentInstallment,
            totalInstallments: $this->totalInstallments,
            status: $this->status,
            matcherRegex: $this->matcherRegex,
        );
    }

    public function withParentTransactionId(string $parentTransactionId): self
    {
        return new self(
            accountId: $this->accountId,
            cardId: $this->cardId,
            incomeSourceId: $this->incomeSourceId,
            expenseId: $this->expenseId,
            categoryId: $this->categoryId,
            statementId: $this->statementId,
            parentTransactionId: $parentTransactionId,
            date: $this->date,
            description: $this->description,
            amount: $this->amount,
            direction: $this->direction,
            kind: $this->kind,
            paymentMethod: $this->paymentMethod,
            currentInstallment: $this->currentInstallment,
            totalInstallments: $this->totalInstallments,
            status: $this->status,
            matcherRegex: $this->matcherRegex,
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
            'statement_id' => $this->statementId,
            'parent_transaction_id' => $this->parentTransactionId,
            'date' => $this->date,
            'description' => $this->description,
            'amount' => $this->amount,
            'direction' => $this->direction,
            'kind' => $this->kind,
            'payment_method' => $this->paymentMethod,
            'current_installment' => $this->currentInstallment,
            'total_installments' => $this->totalInstallments,
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
