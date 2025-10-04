<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\Card;
use App\Models\Statement;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\AssignAccountTransactionStatement;
use Banklink\Contracts\Bank;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;

beforeEach(function () {
    $this->account = Account::factory()
        ->has($this->card = Card::factory(state: ['due_day' => 1]))
        ->for($user = User::factory()->create())
        ->create();

    $this->actingAs($user);
});

it('assigns statement to account transactions based on period', function () {
    $statement = Statement::factory()
        ->for($this->account)
        ->for($this->card)
        ->create([
            'period' => '2025-10',
        ]);

    $transaction = transaction(['date' => Carbon::parse('2025-09-01')])
        ->collect()
        ->lazy();

    $data = SynchronizationData::from('::token::')
        ->withBank($this->mock(Bank::class))
        ->withAccount($this->account)
        ->withAccountTransactions($transaction);

    $data = app()->make(AssignAccountTransactionStatement::class)
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
        ->create([
            'period' => '2025-09',
        ]);

    $data = SynchronizationData::from('::token::')
        ->withBank($this->mock(Bank::class))
        ->withAccount($this->account)
        ->withAccountTransactions(transaction()->collect()->lazy());

    $data = app()->make(AssignAccountTransactionStatement::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->statementMap->toBeEmpty();
});

it('ignores card statements when matching account transactions', function () {
    Statement::factory()
        ->for($this->account)
        ->for($this->card)
        ->create([
            'period' => '2025-10',
        ]);

    $statement = Statement::factory()
        ->for($this->account)
        ->create([
            'period' => '2025-10',
            'card_id' => null,
        ]);

    $data = SynchronizationData::from('::token::')
        ->withBank($this->mock(Bank::class))
        ->withAccount($this->account)
        ->withAccountTransactions(transaction()->collect()->lazy());

    $data = app()->make(AssignAccountTransactionStatement::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($data)
        ->statementMap->first()->toBe($statement->id);
});

