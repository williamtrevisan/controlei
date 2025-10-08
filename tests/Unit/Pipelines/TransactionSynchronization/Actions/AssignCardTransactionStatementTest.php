<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\Card;
use App\Models\Statement;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\AssignCardTransactionStatement;
use Banklink\Contracts\Bank;
use Banklink\Entities\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;

beforeEach(function () {
    $this->account = Account::factory()
        ->for($user = User::factory()->create())
        ->create();
    $this->card = Card::factory()
        ->for($this->account)
        ->create(attributes: ['due_day' => 1]);

    $this->actingAs($user);
});

it('assigns statement to card transactions based on period', function () {
    $statement = Statement::factory()
        ->for($this->account)
        ->for($this->card)
        ->create(['period' => '2025-10']);

    $transaction = transaction([
        'holder' => holder(['lastFourDigits' => $statement->card->last_four_digits]),
        'date' => Carbon::parse('2025-09-01'),
    ])
        ->collect()
        ->lazy();

    $data = SynchronizationData::from('::token::')
        ->withBank($this->mock(Bank::class))
        ->withAccount($this->account)
        ->withCardTransactions($transaction);

    $data = app()->make(AssignCardTransactionStatement::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->statementMap->toBeInstanceOf(LazyCollection::class)
        ->statementMap->count()->toBe(1)
        ->statementMap->first()->toBe($statement->id);
});

it('returns empty map when no matching statements found', function () {
    Statement::factory()
        ->for($this->account)
        ->for($this->card)
        ->create(['period' => '2025-09']);

    $data = SynchronizationData::from('::token::')
        ->withBank($this->mock(Bank::class))
        ->withAccount($this->account)
        ->withCardTransactions(transaction()->collect()->lazy());

    $data = app()->make(AssignCardTransactionStatement::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->statementMap->toBeEmpty();
});

it('returns empty map when card not found', function () {
    Statement::factory()
        ->for($this->account)
        ->create(['period' => '2025-10']);

    $data = SynchronizationData::from('::token::')
        ->withBank($this->mock(Bank::class))
        ->withAccount($this->account)
        ->withCardTransactions(transaction()->collect()->lazy());

    $data = app()->make(AssignCardTransactionStatement::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->statementMap->toBeEmpty();
});

