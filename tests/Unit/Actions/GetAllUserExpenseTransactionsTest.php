<?php

use App\Actions\GetAllUserExpenseTransactions;
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

it('returns all outflow purchase transactions for the user', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'description' => 'Grocery',
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'description' => 'Gas',
    ]);

    $transactions = app()->make(GetAllUserExpenseTransactions::class)->execute();

    expect($transactions)->toHaveCount(2);
});

it('includes inflow refund transactions', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'description' => 'Purchase',
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'kind' => 'refund',
        'description' => 'Refund',
    ]);

    $transactions = app()->make(GetAllUserExpenseTransactions::class)->execute();

    expect($transactions)->toHaveCount(2);
});

it('does not return fee transactions', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'description' => 'Purchase',
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'fee',
        'description' => 'Bank Fee',
    ]);

    $transactions = app()->make(GetAllUserExpenseTransactions::class)->execute();

    expect($transactions)->toHaveCount(1);
    expect($transactions->first()->description)->toBe('Purchase');
});

it('does not return cashback transactions', function () {
    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'description' => 'Purchase',
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'inflow',
        'kind' => 'cashback',
        'description' => 'Cashback',
    ]);

    $transactions = app()->make(GetAllUserExpenseTransactions::class)->execute();

    expect($transactions)->toHaveCount(1);
    expect($transactions->first()->description)->toBe('Purchase');
});

it('only returns transactions for the authenticated user', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Transaction::factory()->create([
        'account_id' => $this->account->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'description' => 'My Expense',
    ]);

    Transaction::factory()->create([
        'account_id' => $otherAccount->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'description' => 'Other Expense',
    ]);

    $transactions = app()->make(GetAllUserExpenseTransactions::class)->execute();

    expect($transactions)->toHaveCount(1);
    expect($transactions->first()->description)->toBe('My Expense');
});

it('returns empty collection when user has no expense transactions', function () {
    $transactions = app()->make(GetAllUserExpenseTransactions::class)->execute();

    expect($transactions)->toBeEmpty();
});

