<?php

use App\Filament\Resources\Accounts\Widgets\AccountBalanceStat;
use App\Models\Account;
use App\Models\User;
use Livewire\Livewire;

it('displays positive current balance correctly', function () {
    Account::factory()
        ->for($user = User::factory()->create())
        ->create([
            'balance' => money()->of('3000.00')
        ]);

    $this->actingAs($user);

    Livewire::test(AccountBalanceStat::class)
        ->assertSee('Saldo atual')
        ->assertSee('3.000,00');
});

it('displays negative current balance correctly', function () {
    Account::factory()
        ->for($user = User::factory()->create())
        ->create([
            'balance' => money()->of('2000.00')->negated()
        ]);

    $this->actingAs($user);

    Livewire::test(AccountBalanceStat::class)
        ->assertSee('Saldo atual')
        ->assertSeeText("-R$\u{A0}2.000,00");
});

it('displays zero balance correctly', function () {
    Account::factory()
        ->for($user = User::factory()->create())
        ->create([
            'balance' => 0
        ]);

    $this->actingAs($user);

    Livewire::test(AccountBalanceStat::class)
        ->assertSee('Saldo atual')
        ->assertSee('0,00');
});

it('hides balance when privacy mode is enabled', function () {
    session()->put('hide_sensitive_data', true);

    Account::factory()
        ->for($user = User::factory()->create())
        ->create([
            'balance' => 0
        ]);

    $this->actingAs($user);

    Livewire::test(AccountBalanceStat::class)
        ->assertSee('****')
        ->assertDontSee('0,00');
});

it('only shows transactions for the authenticated user', function () {
    Account::factory()
        ->for(User::factory()->create())
        ->create([
            'balance' => money()->of('5000.00')
        ]);

    Account::factory()
        ->for($user = User::factory()->create())
        ->create([
            'balance' => money()->of('1000.00')
        ]);

    $this->actingAs($user);

    Livewire::test(AccountBalanceStat::class)
        ->assertSee('1.000,00')
        ->assertDontSee('6.000,00');
});

it('shows description about total balance', function () {
    Livewire::test(AccountBalanceStat::class)
        ->assertSee('Saldo disponível na sua conta bancária');
});

