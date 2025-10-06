<?php

use App\Actions\ClassifyExpenses;
use App\Models\Account;
use App\Models\Expense;
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

it('assigns expenses to transactions based on matcher regex', function () {
    // Create an expense with a matcher regex
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Netflix Subscription',
        'matcher_regex' => '/netflix/i',
        'active' => true,
        'average_amount' => 5000,
    ]);

    // Create a transaction that should match
    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'NETFLIX.COM*BR',
        'direction' => 'outflow',
        'kind' => 'purchase',
        'expense_id' => null,
    ]);

    // Execute the action
    app()->make(ClassifyExpenses::class)->execute();

    // Assert the transaction was assigned to the expense
    expect($transaction->fresh()->expense_id)->toBe($expense->id);
});

it('does not assign expenses to inflow transactions', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Test Expense',
        'matcher_regex' => '/test/i',
        'active' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'TEST PAYMENT',
        'direction' => 'inflow',
        'kind' => 'purchase',
        'expense_id' => null,
    ]);

    app()->make(ClassifyExpenses::class)->execute();

    expect($transaction->fresh()->expense_id)->toBeNull();
});

it('does not assign expenses to non-purchase transactions', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Test Expense',
        'matcher_regex' => '/test/i',
        'active' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'TEST PAYMENT',
        'direction' => 'outflow',
        'kind' => 'fee',
        'expense_id' => null,
    ]);

    app()->make(ClassifyExpenses::class)->execute();

    expect($transaction->fresh()->expense_id)->toBeNull();
});

it('does not assign expenses when matcher regex is empty', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Test Expense',
        'matcher_regex' => '',
        'active' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'TEST PAYMENT',
        'direction' => 'outflow',
        'kind' => 'purchase',
        'expense_id' => null,
    ]);

    app()->make(ClassifyExpenses::class)->execute();

    expect($transaction->fresh()->expense_id)->toBeNull();
});

it('matches multiple transactions to the same expense', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Uber',
        'matcher_regex' => '/uber/i',
        'active' => true,
    ]);

    $transaction1 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'UBER *TRIP',
        'direction' => 'outflow',
        'kind' => 'purchase',
        'expense_id' => null,
    ]);

    $transaction2 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'UBER EATS',
        'direction' => 'outflow',
        'kind' => 'purchase',
        'expense_id' => null,
    ]);

    app()->make(ClassifyExpenses::class)->execute();

    expect($transaction1->fresh()->expense_id)->toBe($expense->id);
    expect($transaction2->fresh()->expense_id)->toBe($expense->id);
});

it('matches transaction to the first matching expense', function () {
    $expense1 = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Food',
        'matcher_regex' => '/food/i',
        'active' => true,
    ]);

    $expense2 = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Restaurant',
        'matcher_regex' => '/food|restaurant/i',
        'active' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'FOOD PLACE',
        'direction' => 'outflow',
        'kind' => 'purchase',
        'expense_id' => null,
    ]);

    app()->make(ClassifyExpenses::class)->execute();

    // Should match the first one found
    expect($transaction->fresh()->expense_id)->toBeIn([$expense1->id, $expense2->id]);
});

it('updates existing expense assignment with new match', function () {
    $oldExpense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Old Expense',
        'matcher_regex' => '/old/i',
        'active' => true,
    ]);

    $newExpense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'New Expense',
        'matcher_regex' => '/spotify/i',
        'active' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'SPOTIFY PREMIUM',
        'direction' => 'outflow',
        'kind' => 'purchase',
        'expense_id' => $oldExpense->id,
    ]);

    app()->make(ClassifyExpenses::class)->execute();

    expect($transaction->fresh()->expense_id)->toBe($newExpense->id);
});

it('handles transactions with special characters in description', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Shopping',
        'matcher_regex' => '/\$hop/i',
        'active' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'description' => 'THE $HOP STORE',
        'direction' => 'outflow',
        'kind' => 'purchase',
        'expense_id' => null,
    ]);

    app()->make(ClassifyExpenses::class)->execute();

    expect($transaction->fresh()->expense_id)->toBe($expense->id);
});

it('only processes transactions for the authenticated user', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'My Expense',
        'matcher_regex' => '/test/i',
        'active' => true,
    ]);

    // Create a transaction for another user
    $this->actingAs($otherUser);
    $otherTransaction = Transaction::factory()->create([
        'account_id' => $otherAccount->id,
        'description' => 'TEST PAYMENT',
        'direction' => 'outflow',
        'kind' => 'purchase',
        'expense_id' => null,
    ]);

    // Switch back to original user and classify
    $this->actingAs($this->user);
    app()->make(ClassifyExpenses::class)->execute();

    // Other user's transaction should not be affected
    expect($otherTransaction->fresh()->expense_id)->toBeNull();
});

