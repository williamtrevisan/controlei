<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\ExtractCardTransactions;
use Banklink\Contracts\Bank;
use Banklink\Entities\Account as BankAccount;
use Illuminate\Support\LazyCollection;

it('extracts card transactions from all card statements', function () {
    $this->actingAs(User::factory()->create());

    $data = SynchronizationData::from('::token::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn(account(['cards' => new class implements \Banklink\Accessors\Contracts\CardsAccessor
                {
                    public function all(): \Illuminate\Support\Collection
                    {
                        return card()->collect();
                    }

                    public function firstWhere(string $key, mixed $value): ?\Banklink\Entities\Card
                    {
                    }
                }]))
                ->getMock()
        )
        ->withAccount(Account::factory()->create());

    $data = app()->make(ExtractCardTransactions::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->cardTransactions->toBeInstanceOf(LazyCollection::class)
        ->cardTransactions->count()->toBe(1);
});

it('returns empty lazy collection when no card transactions found', function () {
    $this->actingAs(User::factory()->create());

    $data = SynchronizationData::from('::token::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn(account(['cards' => new class implements \Banklink\Accessors\Contracts\CardsAccessor
                {
                    public function all(): \Illuminate\Support\Collection
                    {
                        return card(['statements' => new class implements \Banklink\Accessors\Contracts\StatementsAccessor
                        {
                            public function all(): \Illuminate\Support\Collection
                            {
                                return statement(['holders' => holder(['transactions' => fn () => collect()])->collect()])
                                    ->collect();
                            }
                        }])->collect();
                    }

                    public function firstWhere(string $key, mixed $value): ?\Banklink\Entities\Card
                    {
                    }
                }]))
                ->getMock()
        )
        ->withAccount(Account::factory()->create());

    $data = app()->make(ExtractCardTransactions::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->cardTransactions->toBeInstanceOf(LazyCollection::class)
        ->cardTransactions->count()->toBe(0);
});

