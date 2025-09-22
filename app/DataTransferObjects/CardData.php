<?php

namespace App\DataTransferObjects;

use App\Enums\CardBrand;
use App\Enums\CardType;
use Banklink\Entities\Card;
use Banklink\Entities\Holder;

class CardData
{
    public function __construct(
        public int $accountId,
        public string $lastFourDigits,
        public CardType $type = CardType::Credit,
        public ?CardBrand $brand = null,
        public ?string $limit = null,
        public ?int $dueDay = null,
        public ?string $matcherRegex = null,
    ) {
    }

    public static function fromBankCard(Card $bankCard, int $accountId): self
    {
        return new self(
            accountId: $accountId,
            lastFourDigits: $bankCard->lastFourDigits(),
            type: CardType::Credit,
            brand: CardBrand::from($bankCard->brand()->value),
            limit: $bankCard->limit()->total()->getAmount(),
            dueDay: $bankCard->dueDay(),
        );
    }

    public static function fromHolder(Holder $holder, int $accountId): self
    {
        return new self(
            accountId: $accountId,
            lastFourDigits: $holder->lastFourDigits(),
        );
    }
}
