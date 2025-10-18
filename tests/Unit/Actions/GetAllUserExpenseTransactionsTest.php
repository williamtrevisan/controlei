<?php

use App\Actions\GetAllUserExpenseTransactions;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->account = Account::factory()
        ->for($this->user = User::factory()->create())
        ->create();

    $this->actingAs($this->user);
});

it('returns all outflow purchase transactions for the user', function () {
    Transaction::factory(count: 2)
        ->expense()
        ->sequence(
            ['description' => 'Grocery'],
            ['description' => 'Gas'],
        )
        ->for($this->account)
        ->createQuietly();

    $transactions = app()->make(GetAllUserExpenseTransactions::class)
        ->execute();

    expect($transactions)
        ->toHaveCount(2);
});

it('includes inflow refund transactions', function () {
    Transaction::factory(count: 2)
        ->expense()
        ->sequence(
            ['description' => 'Purchase'],
            [
                'description' => 'Refund',
                'direction' => 'inflow',
                'kind' => 'refund',
            ],
        )
        ->for($this->account)
        ->createQuietly();

    $transactions = app()->make(GetAllUserExpenseTransactions::class)
        ->execute();

    expect($transactions)
        ->toHaveCount(2);
});

it('does not return fee transactions', function () {
    Transaction::factory(count: 2)
        ->expense()
        ->sequence(
            ['description' => 'Purchase'],
            [
                'description' => 'Bank Fee',
                'kind' => 'fee',
            ],
        )
        ->for($this->account)
        ->createQuietly();

    $transactions = app()->make(GetAllUserExpenseTransactions::class)
        ->execute();

    expect($transactions)
        ->toHaveCount(1)
        ->first()->description->toBe('Purchase');
});

it('does not return cashback transactions', function () {
    Transaction::factory(count: 2)
        ->expense()
        ->sequence(
            ['description' => 'Purchase'],
            [
                'description' => 'Cashback',
                'kind' => 'cashback',
            ],
        )
        ->for($this->account)
        ->createQuietly();

    $transactions = app()->make(GetAllUserExpenseTransactions::class)
        ->execute();

    expect($transactions)
        ->toHaveCount(1)
        ->first()->description->toBe('Purchase');
});

it('only returns transactions for the authenticated user', function () {
    $account = Account::factory()
        ->for($this->user = User::factory()->create())
        ->create();

    Transaction::factory()
        ->expense()
        ->for($this->account)
        ->createQuietly([
            'description' => 'My Expense',
        ]);

    Transaction::factory()
        ->expense()
        ->for($account)
        ->createQuietly([
            'description' => 'Other Expense',
        ]);

    $transactions = app()->make(GetAllUserExpenseTransactions::class)
        ->execute();

    expect($transactions)
        ->toHaveCount(1)
        ->first()->description->toBe('My Expense');
});

it('returns empty collection when user has no expense transactions', function () {
    $transactions = app()->make(GetAllUserExpenseTransactions::class)
        ->execute();

    expect($transactions)
        ->toBeEmpty();
});

