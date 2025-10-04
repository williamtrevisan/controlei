<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\IncomeSource;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\AssignIncomeSource;
use Banklink\Contracts\Bank;
use Tests\Support\Factories\TransactionDataFactory;

it('assigns income source id to transactions when regex matches', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();

    $incomeSource = IncomeSource::factory()
        ->for($user)
        ->create([
            'name' => 'Salary',
            'matcher_regex' => '/salary|payroll/i',
            'active' => true,
        ]);

    $transaction = factory(TransactionDataFactory::class)
        ->create(attributes: [
            'accountId' => $account->id,
            'description' => 'COMPANY PAYROLL DEPOSIT',
        ]);

    $data = app()->make(AssignIncomeSource::class)
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
        ->transactions->first()->incomeSourceId->toBe($incomeSource->id);
});

it('returns null when income source does not match', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();

    IncomeSource::factory()
        ->for($user)
        ->create([
            'name' => 'Salary',
            'matcher_regex' => '/salary|payroll/i',
            'active' => true,
        ]);

    $transaction = factory(TransactionDataFactory::class)
        ->create(attributes: [
            'accountId' => $account->id,
            'description' => 'RANDOM TRANSFER',
        ]);

    $data = app()->make(AssignIncomeSource::class)
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
        ->transactions->first()->incomeSourceId->toBeNull();
});

it('returns null when income source has no matcher regex', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();

    IncomeSource::factory()
        ->for($user)
        ->create();

    $transaction = factory(TransactionDataFactory::class)
        ->create(attributes: [
            'accountId' => $account->id,
            'description' => 'SOME TRANSACTION',
        ]);

    $data = app()->make(AssignIncomeSource::class)
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
        ->transactions->first()->incomeSourceId->toBeNull();
});

