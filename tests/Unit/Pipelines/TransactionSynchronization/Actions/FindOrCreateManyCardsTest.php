<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\FindOrCreateManyCards;
use Banklink\Accessors\Contracts\CardsAccessor;
use Banklink\Contracts\Bank;

it('creates card when it does not exist', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();

    $this->assertDatabaseCount(Card::class, 0);

    $data = SynchronizationData::from('::fake::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn(account([
                    'cards' => new class implements CardsAccessor
                    {
                        public function all(): \Illuminate\Support\Collection
                        {
                            return card([
                                'lastFourDigits' => '9999',
                                'dueDay' => 15,
                                'statements' => new class implements \Banklink\Accessors\Contracts\StatementsAccessor
                                {
                                    public function all(): \Illuminate\Support\Collection
                                    {
                                        return statement([
                                            'holders' => holder([
                                                'lastFourDigits' => '9999',
                                                'transactions' => fn () => collect(),
                                            ])->collect(),
                                        ])->collect();
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
        ->withAccount($account);

    $result = app()->make(FindOrCreateManyCards::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($result)->toBeInstanceOf(SynchronizationData::class);

    $this->assertDatabaseCount(Card::class, 1);
    $this->assertDatabaseHas(Card::class, [
        'account_id' => $account->id,
        'last_four_digits' => '9999',
        'due_day' => 15,
    ]);
});

it('finds existing card without creating duplicate', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();
    $existingCard = Card::factory()
        ->for($account)
        ->create([
            'last_four_digits' => '9999',
            'due_day' => 15,
        ]);

    $this->assertDatabaseCount(Card::class, 1);

    $data = SynchronizationData::from('::fake::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn(account([
                    'cards' => new class implements CardsAccessor
                    {
                        public function all(): \Illuminate\Support\Collection
                        {
                            return card([
                                'lastFourDigits' => '9999',
                                'dueDay' => 15,
                                'statements' => new class implements \Banklink\Accessors\Contracts\StatementsAccessor
                                {
                                    public function all(): \Illuminate\Support\Collection
                                    {
                                        return statement([
                                            'holders' => holder([
                                                'name' => '::fake::',
                                                'lastFourDigits' => '9999',
                                                'transactions' => fn () => collect(),
                                            ])->collect(),
                                        ])->collect();
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
        ->withAccount($account);

    $result = app()->make(FindOrCreateManyCards::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($result)->toBeInstanceOf(SynchronizationData::class);

    $this->assertDatabaseCount(Card::class, 1);
    $this->assertDatabaseHas(Card::class, [
        'id' => $existingCard->id,
        'account_id' => $account->id,
        'last_four_digits' => '9999',
        'due_day' => 15,
    ]);
});
