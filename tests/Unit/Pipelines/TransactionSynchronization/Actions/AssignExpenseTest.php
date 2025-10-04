<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\Expense;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\AssignExpense;
use Banklink\Contracts\Bank;
use Tests\Support\Factories\TransactionDataFactory;

it('assigns expense id to transactions when regex matches', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();

    $expense = Expense::factory()
        ->for($user)
        ->create([
            'description' => 'Netflix Subscription',
            'matcher_regex' => '/netflix/i',
            'active' => true,
        ]);

    $transaction = factory(TransactionDataFactory::class)
        ->create(attributes: [
            'accountId' => $account->id,
            'description' => 'NETFLIX.COM SUBSCRIPTION',
        ]);

    $data = app()->make(AssignExpense::class)
        ->handle(
            new SynchronizationData(
                token: '::fake::',
                bank: app()->make(Bank::class),
                account: $account,
                transactions: collect([$transaction])->lazy(),
            ),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    expect($data)
        ->transactions->first()->expenseId->toBe($expense->id);
});

it('returns null when expense does not match', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();

    Expense::factory()
        ->for($user)
        ->create([
            'description' => 'Netflix Subscription',
            'matcher_regex' => '/netflix/i',
            'active' => true,
        ]);

    $transaction = factory(TransactionDataFactory::class)
        ->create(attributes: [
            'accountId' => $account->id,
            'description' => 'SPOTIFY SUBSCRIPTION',
        ]);

    $data = app()->make(AssignExpense::class)
        ->handle(
            new SynchronizationData(
                token: '::fake::',
                bank: app()->make(Bank::class),
                account: $account,
                transactions: collect([$transaction])->lazy(),
            ),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    expect($data)
        ->transactions->first()->expenseId->toBeNull();
});

it('returns null when expense has no matcher regex', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();

    Expense::factory()
        ->for($user)
        ->create();

    $transaction = factory(TransactionDataFactory::class)
        ->create(attributes: [
            'accountId' => $account->id,
            'description' => 'SOME TRANSACTION',
        ]);

    $data = app()->make(AssignExpense::class)
        ->handle(
            new SynchronizationData(
                token: '::fake::',
                bank: app()->make(Bank::class),
                account: $account,
                transactions: collect([$transaction])->lazy(),
            ),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    expect($data)
        ->transactions->first()->expenseId->toBeNull();
});

