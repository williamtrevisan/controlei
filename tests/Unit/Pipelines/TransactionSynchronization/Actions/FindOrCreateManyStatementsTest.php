<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\Card;
use App\Models\Statement;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\FindOrCreateManyStatements;
use Banklink\Contracts\Bank;
use Illuminate\Support\Carbon;
use Tests\Support\Factories\TransactionDataFactory;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2025-10-01'));

    $this->card = Card::factory()
        ->for(
            $this->account = Account::factory()
                ->for($user = User::factory()->create())
                ->create()
        )
        ->create([
            'due_day' => 1,
        ]);

    $this->actingAs($user);
});

it('creates statements when they do not exist', function () {
    $transactions = factory(TransactionDataFactory::class)
        ->sequence([
            ['date' => now()->subDays(8)], // 2025-10
            ['date' => now()->subDays(6)], // 2025-11
            ['date' => now()], // 2025-11
            ['date' => now()->addMonth()], // 2025-12
            ['date' => now()->addMonth()->addDays(15)], // 2025-12
        ])
        ->create(count: 5, attributes: [
            'accountId' => $this->account->id,
            'cardId' => $this->card->id,
        ]);

    $this->assertDatabaseCount(Statement::class, 0);

    $card = $this->card;

    $data = SynchronizationData::from('::fake::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn(account([
                    'cards' => new class($card) implements \Banklink\Accessors\Contracts\CardsAccessor
                    {
                        public function __construct(private Card $card)
                        {
                        }

                        public function all(): \Illuminate\Support\Collection
                        {
                            return card([
                                'lastFourDigits' => $this->card->last_four_digits,
                                'statements' => new class($this->card) implements \Banklink\Accessors\Contracts\StatementsAccessor
                                {
                                    public function __construct(private Card $card)
                                    {
                                    }

                                    public function all(): \Illuminate\Support\Collection
                                    {
                                        return collect([
                                            statement([
                                                'period' => \Banklink\Entities\StatementPeriod::fromString('2025-10'),
                                                'card' => card(['lastFourDigits' => $this->card->last_four_digits]),
                                            ]),
                                            statement([
                                                'period' => \Banklink\Entities\StatementPeriod::fromString('2025-11'),
                                                'card' => card(['lastFourDigits' => $this->card->last_four_digits]),
                                            ]),
                                            statement([
                                                'period' => \Banklink\Entities\StatementPeriod::fromString('2025-12'),
                                                'card' => card(['lastFourDigits' => $this->card->last_four_digits]),
                                            ]),
                                        ]);
                                    }
                                },
                            ])->collect();
                        }

                        public function firstWhere(string $key, mixed $value): ?\Banklink\Entities\Card
                        {
                        }
                    },
                ]))
                ->getMock()
        )
        ->withAccount($this->account)
        ->withTransactions(collect($transactions)->lazy());

    $result = app()->make(FindOrCreateManyStatements::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($result)->toBeInstanceOf(SynchronizationData::class);

    $this->assertDatabaseCount(Statement::class, 3);
});

it('finds existing statements and does not recreate them', function () {
    $existingStatements = Statement::factory(count: 2)
        ->for($this->account)
        ->for($this->card)
        ->sequence(
            ['period' => now()->format('Y-m')], // 2025-10
            ['period' => now()->addMonth()->format('Y-m')] // 2025-11
        )
        ->create();

    $transactions = factory(TransactionDataFactory::class)
        ->sequence([
            ['date' => now()->subDays(8)], // 2025-10
            ['date' => now()->subDays(6)], // 2025-11
            ['date' => now()], // 2025-11
            ['date' => now()->addMonth()], // 2025-12
            ['date' => now()->addMonth()->addDays(15)],  // 2025-12
        ])
        ->create(count: 5, attributes: [
            'accountId' => $this->account->id,
            'cardId' => $this->card->id,
        ]);

    $this->assertDatabaseCount(Statement::class, 2);

    $card = $this->card;

    $data = SynchronizationData::from('::fake::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn(account([
                    'cards' => new class($card) implements \Banklink\Accessors\Contracts\CardsAccessor
                    {
                        public function __construct(private Card $card)
                        {
                        }

                        public function all(): \Illuminate\Support\Collection
                        {
                            return card([
                                'lastFourDigits' => $this->card->last_four_digits,
                                'statements' => new class($this->card) implements \Banklink\Accessors\Contracts\StatementsAccessor
                                {
                                    public function __construct(private Card $card)
                                    {
                                    }

                                    public function all(): \Illuminate\Support\Collection
                                    {
                                        return collect([
                                            statement([
                                                'period' => \Banklink\Entities\StatementPeriod::fromString('2025-10'),
                                                'card' => card(['lastFourDigits' => $this->card->last_four_digits]),
                                            ]),
                                            statement([
                                                'period' => \Banklink\Entities\StatementPeriod::fromString('2025-11'),
                                                'card' => card(['lastFourDigits' => $this->card->last_four_digits]),
                                            ]),
                                            statement([
                                                'period' => \Banklink\Entities\StatementPeriod::fromString('2025-12'),
                                                'card' => card(['lastFourDigits' => $this->card->last_four_digits]),
                                            ]),
                                        ]);
                                    }
                                },
                            ])->collect();
                        }

                        public function firstWhere(string $key, mixed $value): ?\Banklink\Entities\Card
                        {
                        }
                    },
                ]))
                ->getMock()
        )
        ->withAccount($this->account)
        ->withTransactions(collect($transactions)->lazy());

    $result = app()->make(FindOrCreateManyStatements::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($result)->toBeInstanceOf(SynchronizationData::class);

    $this->assertDatabaseCount(Statement::class, 3);
    $this->assertDatabaseHas(Statement::class, ['id' => $existingStatements->first()->id]);
    $this->assertDatabaseHas(Statement::class, ['id' => $existingStatements->last()->id]);
});

