<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class TransactionMemberData implements Arrayable
{
    public readonly string $id;

    public readonly Carbon $createdAt;

    public readonly Carbon $updatedAt;

    public function __construct(
        public string $transactionId,
        public int $ownerId,
        public int $memberId,
        public Carbon $sharedAt,
    ) {
        $this->id = Str::uuid7()->toString();
        $this->createdAt = now();
        $this->updatedAt = now();
    }

    public static function from(
        string $transactionId,
        int $ownerId,
        int $memberId,
        ?Carbon $sharedAt = null,
    ): self {
        return new self(
            transactionId: $transactionId,
            ownerId: $ownerId,
            memberId: $memberId,
            sharedAt: $sharedAt ?? now(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transactionId,
            'owner_id' => $this->ownerId,
            'member_id' => $this->memberId,
            'shared_at' => $this->sharedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}