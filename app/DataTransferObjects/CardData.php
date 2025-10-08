<?php

namespace App\DataTransferObjects;

use App\Enums\CardType;
use App\Models\Account;
use Banklink\Entities\Card;
use Banklink\Entities\Holder;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

readonly class CardData implements Arrayable
{
    public string $id;

    public Carbon $createdAt;

    public Carbon $updatedAt;

    public function __construct(
        public string $accountId,
        public string $lastFourDigits,
        public string $type,
        public ?string $brand = null,
        public int $limit = 0,
        public int $dueDay = 1,
        public string $matcherRegex = '',
    ) {
        $this->id = Str::uuid7()->toString();
        $this->createdAt = now();
        $this->updatedAt = now();
    }

    /**
     * @param Holder $holder
     * @param Account $account
     * @param Collection<int, Card> $cards
     * @return self
     */
    public static function from(Holder $holder, Account $account, Collection $cards): self
    {
        $card = $cards->first(function ($card) use ($holder) {
            return collect($card->statements()->all())
                ->flatMap(fn($statement) => $statement->holders())
                ->contains(fn($h) => $h->lastFourDigits() === $holder->lastFourDigits());
        });

        return new self(
            accountId: $account->id,
            lastFourDigits: $holder->lastFourDigits(),
            type: CardType::Credit->value,
            brand: $card?->brand()->value,
            limit: $card?->limit()->total()->getMinorAmount()->toInt() ?? 0,
            dueDay: $card?->dueDay() ?? 1,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->accountId,
            'last_four_digits' => $this->lastFourDigits,
            'type' => $this->type,
            'brand' => $this->brand,
            'limit' => $this->limit,
            'due_day' => $this->dueDay,
            'matcher_regex' => $this->matcherRegex,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
