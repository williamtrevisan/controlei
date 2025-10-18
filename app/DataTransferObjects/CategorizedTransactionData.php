<?php

namespace App\DataTransferObjects;

readonly class CategorizedTransactionData
{
    public function __construct(
        public string $id,
        public int $categoryId,
    ) {
    }

    public static function from(array $transaction): self
    {
        return new self(
            id: $transaction['id'],
            categoryId: $transaction['category_id'],
        );
    }
}
