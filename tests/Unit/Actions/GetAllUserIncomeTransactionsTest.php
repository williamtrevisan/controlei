<?php

use App\Actions\GetAllUserIncomeTransactions;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

it('returns all inflow transactions for the user', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'description' => 'Salary',
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'description' => 'Bonus',
    ]);

    $transactions = app()->make(GetAllUserIncomeTransactions::class)->execute();

    expect($transactions)->toHaveCount(2);
});

it('does not return outflow transactions', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'description' => 'Income',
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'description' => 'Expense',
    ]);

    $transactions = app()->make(GetAllUserIncomeTransactions::class)->execute();

    expect($transactions)->toHaveCount(1);
    expect($transactions->first()->description)->toBe('Income');
});

it('only returns transactions for the authenticated user', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'description' => 'My Income',
    ]);

    Transaction::factory()->create([
        'account_id' => $otherAccount->id,
        'direction' => 'inflow',
        'description' => 'Other Income',
    ]);

    $transactions = app()->make(GetAllUserIncomeTransactions::class)->execute();

    expect($transactions)->toHaveCount(1);
    expect($transactions->first()->description)->toBe('My Income');
});

it('returns empty collection when user has no income transactions', function () {
    $transactions = app()->make(GetAllUserIncomeTransactions::class)->execute();

    expect($transactions)->toBeEmpty();
});

