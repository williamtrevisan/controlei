<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * @param string-class $class
 * @return \Tests\Support\Factories\Factory
 */
function factory(string $class): Tests\Support\Factories\Factory
{
    return $class::new();
}

function account(array $attributes = []): Banklink\Entities\Account
{
    return new class($attributes) extends Banklink\Entities\Account
    {
        public function __construct(private array $attributes = [])
        {
        }

        public function bank(): string
        {
            return $this->attributes['bank'] ?? 'itau';
        }

        public function agency(): string
        {
            return $this->attributes['agency'] ?? fake()->numerify('####');
        }

        public function number(): string
        {
            return $this->attributes['number'] ?? fake()->numerify('#####');
        }

        public function digit(): string
        {
            return $this->attributes['digit'] ?? fake()->numerify('#');
        }

        public function cards(): \Banklink\Accessors\Contracts\CardsAccessor
        {
            return $this->attributes['cards']
                ?? new Banklink\Accessors\CardsAccessor();
        }

        public function transactions(): \Banklink\Accessors\Contracts\TransactionsAccessor
        {
            return $this->attributes['transactions']
                ?? new Banklink\Accessors\TransactionsAccessor();
        }
    };
}

function card(array $attributes = []): Banklink\Entities\Card
{
    return new class($attributes) extends Banklink\Entities\Card
    {
        public function __construct(private array $attributes = [])
        {
        }

        public function id(): string
        {
            return $this->attributes['id'] ?? fake()->uuid();
        }

        public function name(): string
        {
            return $this->attributes['name'] ?? '::card::';
        }

        public function lastFourDigits(): string
        {
            return $this->attributes['lastFourDigits'] ?? fake()->numerify('####');
        }

        public function brand(): Banklink\Enums\CardBrand
        {
            return $this->attributes['brand'] ?? fake()->randomElement(Banklink\Enums\CardBrand::cases());
        }

        public function limit(): Banklink\Entities\CardLimit
        {
            if (isset($this->attributes['limit'])) {
                return $this->attributes['limit'];
            }

            return new class extends Banklink\Entities\CardLimit
            {
                public function used(): Brick\Money\Money
                {
                    return Brick\Money\Money::of(0, 'BRL');
                }

                public function available(): Brick\Money\Money
                {
                    return Brick\Money\Money::of(0, 'BRL');
                }

                public function total(): Brick\Money\Money
                {
                    return Brick\Money\Money::of(0, 'BRL');
                }
            };
        }

        public function statements(): \Banklink\Accessors\Contracts\StatementsAccessor
        {
            return $this->attributes['statements']
                ?? new Banklink\Accessors\StatementsAccessor(statement());
        }

        public function dueDay(): int
        {
            return $this->attributes['dueDay'] ?? fake()->numberBetween(1, 28);
        }

        public function collect(): Collection
        {
            return collect(Arr::wrap($this));
        }
    };
}

function holder(array $attributes = []): Banklink\Entities\Holder
{
    return new class($attributes) extends Banklink\Entities\Holder
    {
        public function __construct(private array $attributes = [])
        {
        }

        public function statement(): Banklink\Entities\CardStatement
        {
            return $this->attributes['statement'] ?? statement();
        }

        public function name(): string
        {
            return $this->attributes['name'] ?? fake()->name();
        }

        public function lastFourDigits(): string
        {
            return $this->attributes['lastFourDigits'] ?? fake()->numerify('####');
        }

        public function amount(): Brick\Money\Money
        {
            return Brick\Money\Money::of($this->attributes['amount'] ?? 0, 'BRL');
        }

        public function transactions(): Illuminate\Support\Collection
        {
            return value($this->attributes['transactions'] ?? null) ?? collect()->times(1, fn () => transaction());
        }

        public function collect(): Collection
        {
            return collect(Arr::wrap($this));
        }
    };
}

function statement(array $attributes = []): Banklink\Entities\CardStatement
{
    return new class($attributes) extends Banklink\Entities\CardStatement
    {
        public function __construct(private array $attributes = [])
        {
        }

        public function all(): \Illuminate\Support\Collection
        {
            return collect(Arr::wrap($this));
        }

        public function card(): \Banklink\Entities\Card
        {
            return $this->attributes['card'] ?? card();
        }

        public function status(): Banklink\Enums\StatementStatus
        {
            return $this->attributes['status'] ?? Banklink\Enums\StatementStatus::Closed;
        }

        public function dueDate(): Illuminate\Support\Carbon
        {
            return $this->attributes['dueDate'] ?? now();
        }

        public function closingDate(): Illuminate\Support\Carbon
        {
            return $this->attributes['closingDate'] ?? now()->subDays(7);
        }

        public function amount(): Brick\Money\Money
        {
            return Brick\Money\Money::of($this->attributes['amount'] ?? 0, 'BRL');
        }

        public function period(): Banklink\Entities\StatementPeriod
        {
            return $this->attributes['period']
                ?? Banklink\Entities\StatementPeriod::fromString(now()->format('Y-m'));
        }

        public function holders(): Illuminate\Support\Collection
        {
            return collect($this->attributes['holders'] ?? [])
                ->when(fn (Collection $collection) => $collection->isEmpty(), fn () => collect()->times(1, fn () => holder()));
        }

        public function collect(): Collection
        {
            return collect(Arr::wrap($this));
        }
    };
}

function transaction(array $attributes = []): Banklink\Entities\Transaction
{
    return new class($attributes) extends Banklink\Entities\Transaction
    {
        public function __construct(private array $attributes = [])
        {
        }

        public function statement(): ?Banklink\Entities\CardStatement
        {
            return $this->attributes['statement'] ?? statement();
        }

        public function holder(): ?Banklink\Entities\Holder
        {
            return value($this->attributes['holder'] ?? null) ?? holder();
        }

        public function date(): Illuminate\Support\Carbon
        {
            return $this->attributes['date'] ?? now();
        }

        public function description(): string
        {
            return $this->attributes['description'] ?? fake()->words(3, true);
        }

        public function amount(): Brick\Money\Money
        {
            return Brick\Money\Money::of($this->attributes['amount'] ?? 0, 'BRL');
        }

        public function direction(): Banklink\Enums\TransactionDirection
        {
            return $this->attributes['direction'] ?? fake()->randomElement(Banklink\Enums\TransactionDirection::cases());
        }

        public function kind(): Banklink\Enums\TransactionKind
        {
            return $this->attributes['kind'] ?? fake()->randomElement(Banklink\Enums\TransactionKind::cases());
        }

        public function paymentMethod(): Banklink\Enums\TransactionPaymentMethod
        {
            return $this->attributes['paymentMethod'] ?? fake()->randomElement(Banklink\Enums\TransactionPaymentMethod::cases());
        }

        public function installments(): ?Banklink\Entities\Installment
        {
            return $this->attributes['installments'] ?? null;
        }

        public function isRefund(Banklink\Enums\TransactionType $from): bool
        {
            return $this->attributes['isRefund'] ?? false;
        }

        public function collect(): Collection
        {
            return collect(Arr::wrap($this));
        }
    };
}
