<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\ExtractAccountTransactions;
use Banklink\Accessors\Contracts\TransactionsAccessor;
use Banklink\Contracts\Bank;
use Illuminate\Support\LazyCollection;

it('extracts account transactions between start of year and now', function () {
    $this->actingAs(User::factory()->create());

    $transactions = collect()
        ->times(2, fn () => transaction());
    $account = account(['transactions' => new class($transactions) implements TransactionsAccessor {
        public function __construct(private \Illuminate\Support\Collection $transactions)
        {
        }

        public function between(\Illuminate\Support\Carbon $from, \Illuminate\Support\Carbon $to): \Illuminate\Support\Collection
        {
            return $this->transactions;
        }

        public function today(): \Illuminate\Support\Collection
        {
        }
    }]);

    $data = SynchronizationData::from('::token::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn($account)
                ->getMock()
        )
        ->withAccount(Account::factory()->create());

    $data = app()->make(ExtractAccountTransactions::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->accountTransactions->toBeInstanceOf(LazyCollection::class)
        ->accountTransactions->count()->toBe(2);
});

it('returns empty lazy collection when no transactions found', function () {
    $this->actingAs(User::factory()->create());

    $account = account(['transactions' => new class implements TransactionsAccessor
    {
        public function between(\Illuminate\Support\Carbon $from, \Illuminate\Support\Carbon $to): \Illuminate\Support\Collection
        {
            return collect();
        }

        public function today(): \Illuminate\Support\Collection
        {
        }
    }]);

    $data = SynchronizationData::from('::token::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn($account)
                ->getMock()
        )
        ->withAccount(Account::factory()->create());

    $data = app()->make(ExtractAccountTransactions::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->accountTransactions->toBeInstanceOf(LazyCollection::class)
        ->accountTransactions->count()->toBe(0);
});

