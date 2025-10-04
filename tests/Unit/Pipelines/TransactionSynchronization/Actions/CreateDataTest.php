<?php

use App\DataTransferObjects\SynchronizationData;
use App\DataTransferObjects\TransactionData;
use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\CreateData;
use Banklink\Contracts\Bank;
use Illuminate\Support\LazyCollection;

beforeEach(function () {
    $this->account = Account::factory()
        ->for($user = User::factory()->create())
        ->create();

    $this->actingAs($user);
});

it('creates TransactionData from account transactions', function () {
    $data = SynchronizationData::from('::token::')
        ->withBank($this->mock(Bank::class))
        ->withAccount($this->account)
        ->withAccountTransactions(transaction()->collect()->lazy());

    $data = app()->make(CreateData::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->transactions->toBeInstanceOf(LazyCollection::class)
        ->transactions->count()->toBe(1)
        ->and($data->transactions->first())
            ->toBeInstanceOf(TransactionData::class)
            ->transactions->cardId->toBeNull();
});

it('creates TransactionData from card transactions', function () {
    $card = Card::factory()
        ->for($this->account)
        ->create();

    $transactions = transaction([
        'holder' => fn () => holder(['lastFourDigits' => $card->last_four_digits])
    ])
        ->collect()
        ->lazy();

    $data = SynchronizationData::from('::token::')
        ->withBank($this->mock(Bank::class))
        ->withAccount($this->account)
        ->withCardTransactions($transactions);

    $data = app()->make(CreateData::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->transactions->toBeInstanceOf(LazyCollection::class)
        ->transactions->count()->toBe(1)
        ->and($data->transactions->first())
            ->toBeInstanceOf(TransactionData::class)
            ->cardId->not->toBeNull();
});
