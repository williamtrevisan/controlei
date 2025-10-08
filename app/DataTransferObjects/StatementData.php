<?php

namespace App\DataTransferObjects;

use App\Enums\StatementStatus;
use App\Models\Card;
use Banklink\Entities\CardStatement;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

readonly class StatementData implements Arrayable
{
    public string $id;

    public Carbon $createdAt;

    public Carbon $updatedAt;

    public function __construct(
        public string $accountId,
        public ?string $cardId,
        public ?string $parentStatementId,
        public string $period,
        public Carbon $closingDate,
        public Carbon $dueDate,
        public StatementStatus $status,
        public int $amount,
    ) {
        $this->id = Str::uuid7()->toString();
        $this->createdAt = now();
        $this->updatedAt = now();
    }

    public static function from(CardStatement $statement, Card $card): self
    {
        return new self(
            accountId: $card->account->id,
            cardId: $card->id,
            parentStatementId: null,
            period: $statement->period()->value(),
            closingDate: $statement->closingDate(),
            dueDate: $statement->dueDate(),
            status: StatementStatus::from($statement->status()->value),
            amount: $statement->amount()->getMinorAmount()->toInt(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->accountId,
            'card_id' => $this->cardId,
            'parent_statement_id' => $this->parentStatementId,
            'period' => $this->period,
            'closing_date' => $this->closingDate,
            'due_date' => $this->dueDate,
            'status' => $this->status,
            'amount' => $this->amount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}

