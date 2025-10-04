<?php

use App\DataTransferObjects\SynchronizationData;
use App\Enums\AccountBank;
use App\Models\Account;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\FindOrCreateAccount;
use Banklink\Contracts\Bank;

it('creates account when it does not exist', function () {
    $this->actingAs($user = User::factory()->create());

    $this->assertDatabaseCount(Account::class, 0);

    $data = SynchronizationData::from('::fake::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn(account([
                    'bank' => 'itau',
                    'agency' => '9999',
                    'number' => '99999',
                    'digit' => '9',
                ]))
                ->getMock()
        );

    $result = app()->make(FindOrCreateAccount::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($result)
        ->toBeInstanceOf(SynchronizationData::class)
        ->account->toBeInstanceOf(Account::class);

    $this->assertDatabaseCount(Account::class, 1);
    $this->assertDatabaseHas(Account::class, [
        'user_id' => $user->id,
        'bank' => AccountBank::Itau->value,
        'agency' => '9999',
        'account' => '99999',
        'account_digit' => '9',
    ]);
});

it('finds existing account without creating duplicate', function () {
    $this->actingAs($user = User::factory()->create());

    $existingAccount = Account::factory()
        ->for($user)
        ->create([
            'bank' => AccountBank::Itau,
            'agency' => '9999',
            'account' => '99999',
            'account_digit' => '9',
        ]);

    $this->assertDatabaseCount(Account::class, 1);

    $data = SynchronizationData::from('::fake::')
        ->withBank(
            $this->mock(Bank::class)
                ->expects('account')
                ->andReturn(account([
                    'bank' => 'itau',
                    'agency' => '9999',
                    'number' => '99999',
                    'digit' => '9',
                ]))
                ->getMock()
        );

    $result = app()->make(FindOrCreateAccount::class)
        ->handle($data, fn (SynchronizationData $data): SynchronizationData => $data);

    expect($result)
        ->toBeInstanceOf(SynchronizationData::class)
        ->account->toBeInstanceOf(Account::class);

    $this->assertDatabaseCount(Account::class, 1);
    $this->assertDatabaseHas(Account::class, [
        'id' => $existingAccount->id,
        'user_id' => $user->id,
        'bank' => AccountBank::Itau->value,
        'agency' => '9999',
        'account' => '99999',
        'account_digit' => '9',
    ]);
});
