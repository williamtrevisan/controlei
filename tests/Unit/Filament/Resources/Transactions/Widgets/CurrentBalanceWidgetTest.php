<?php

use App\Filament\Resources\Transactions\Widgets\Stats\AccountBalanceStat;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

it('displays positive current balance correctly', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'amount' => 500000, // R$ 5,000.00
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => 200000, // R$ 2,000.00
    ]);

    $widget = Livewire::test(AccountBalanceStat::class);

    $widget->assertSee('Saldo Atual');
    $widget->assertSee('3.000,00'); // R$ 3,000.00 balance
});

it('displays negative current balance correctly', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'amount' => 100000, // R$ 1,000.00
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => 300000, // R$ 3,000.00
    ]);

    $widget = Livewire::test(AccountBalanceStat::class);

    $widget->assertSee('Saldo Atual');
    $widget->assertSee('-2.000,00'); // R$ -2,000.00 balance
});

it('displays zero balance correctly', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'amount' => 100000, // R$ 1,000.00
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => 100000, // R$ 1,000.00
    ]);

    $widget = Livewire::test(AccountBalanceStat::class);

    $widget->assertSee('Saldo Atual');
    $widget->assertSee('0,00'); // R$ 0.00 balance
});

it('hides balance when privacy mode is enabled', function () {
    session()->put('hide_sensitive_data', true);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'amount' => 500000,
    ]);

    $widget = Livewire::test(AccountBalanceStat::class);

    $widget->assertSee('****');
    $widget->assertDontSee('5.000,00');
});

it('only shows transactions for the authenticated user', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    // Current user transaction
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'amount' => 100000, // R$ 1,000.00
    ]);

    // Other user transaction (should not be included)
    Transaction::factory()->create([
        'account_id' => $otherAccount->id,
        'direction' => 'inflow',
        'amount' => 500000, // R$ 5,000.00
    ]);

    $widget = Livewire::test(AccountBalanceStat::class);

    $widget->assertSee('1.000,00'); // Only current user's balance
    $widget->assertDontSee('6.000,00'); // Should not include other user
});

it('calculates balance across multiple transactions', function () {
    // Multiple incomes
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'amount' => 300000, // R$ 3,000.00
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'amount' => 200000, // R$ 2,000.00
    ]);

    // Multiple expenses
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => 150000, // R$ 1,500.00
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => 100000, // R$ 1,000.00
    ]);

    $widget = Livewire::test(AccountBalanceStat::class);

    // Total: (3000 + 2000) - (1500 + 1000) = 2500
    $widget->assertSee('2.500,00');
});

it('shows description about total balance', function () {
    $widget = Livewire::test(AccountBalanceStat::class);

    $widget->assertSee('Saldo total considerando todas as transações');
});

