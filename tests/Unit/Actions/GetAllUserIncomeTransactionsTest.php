<?php

use App\Actions\GetAllUserIncomeTransactions;
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

it('returns all inflow transactions for the user', function () {
    Transaction::factory(count: 2)
        ->inflow()
        ->sequence(
            ['description' => 'Salary'],
            ['description' => 'Bonus'],
        )
        ->for($this->account)
        ->createQuietly();

    $transactions = app()->make(GetAllUserIncomeTransactions::class)
        ->execute();

    expect($transactions)
        ->toHaveCount(2);
});

it('does not return outflow transactions', function () {
    Transaction::factory()
        ->inflow()
        ->for($this->account)
        ->createQuietly([
            'description' => 'Income',
        ]);

    Transaction::factory()
        ->expense()
        ->for($this->account)
        ->createQuietly([
            'description' => 'Expense'
        ]);

    $transactions = app()->make(GetAllUserIncomeTransactions::class)
        ->execute();

    expect($transactions)
        ->toHaveCount(1)
        ->first()->description->toBe('Income');
});

it('only returns transactions for the authenticated user', function () {
    $account = Account::factory()
        ->for(User::factory()->create())
        ->create();

    Transaction::factory()
        ->inflow()
        ->for($this->account)
        ->createQuietly([
            'description' => 'My Income',
        ]);

    Transaction::factory()
        ->inflow()
        ->for($account)
        ->createQuietly([
            'description' => 'Other Income',
        ]);

    $transactions = app()->make(GetAllUserIncomeTransactions::class)
        ->execute();

    expect($transactions)
        ->toHaveCount(1)
        ->first()->description->toBe('My Income');
});

it('returns empty collection when user has no income transactions', function () {
    $transactions = app()->make(GetAllUserIncomeTransactions::class)
        ->execute();

    expect($transactions)
        ->toBeEmpty();
});

