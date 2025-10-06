<?php

use App\Actions\UpdateTransactionExpense;
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

it('batch updates multiple transactions with expense id', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Netflix',
    ]);

    $transaction1 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'expense_id' => null,
    ]);

    $transaction2 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'expense_id' => null,
    ]);

    $result = app()->make(UpdateTransactionExpense::class)
        ->execute(collect([$transaction1, $transaction2]), $expense);

    expect($result)->toBe(2);
    expect($transaction1->fresh()->expense_id)->toBe($expense->id);
    expect($transaction2->fresh()->expense_id)->toBe($expense->id);
});

it('batch updates transaction expense from one to another', function () {
    $oldExpense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Old Expense',
    ]);

    $newExpense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'New Expense',
    ]);

    $transaction1 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'expense_id' => $oldExpense->id,
    ]);

    $transaction2 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'expense_id' => $oldExpense->id,
    ]);

    $result = app()->make(UpdateTransactionExpense::class)
        ->execute(collect([$transaction1, $transaction2]), $newExpense);

    expect($result)->toBe(2);
    expect($transaction1->fresh()->expense_id)->toBe($newExpense->id);
    expect($transaction2->fresh()->expense_id)->toBe($newExpense->id);
});

it('removes expense from multiple transactions when null is passed', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Expense',
    ]);

    $transaction1 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'expense_id' => $expense->id,
    ]);

    $transaction2 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'expense_id' => $expense->id,
    ]);

    $result = app()->make(UpdateTransactionExpense::class)
        ->execute(collect([$transaction1, $transaction2]), null);

    expect($result)->toBe(2);
    expect($transaction1->fresh()->expense_id)->toBeNull();
    expect($transaction2->fresh()->expense_id)->toBeNull();
});

it('returns number of updated transactions', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $transactions = Transaction::factory()->count(5)->create([
        'account_id' => $this->account->id,
    ]);

    $result = app()->make(UpdateTransactionExpense::class)
        ->execute($transactions, $expense);

    expect($result)->toBe(5);
});

it('returns 0 when no transactions provided', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $result = app()->make(UpdateTransactionExpense::class)
        ->execute(collect(), $expense);

    expect($result)->toBe(0);
});

it('handles single transaction batch update', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Expense',
    ]);

    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'expense_id' => null,
    ]);

    $result = app()->make(UpdateTransactionExpense::class)
        ->execute(collect([$transaction]), $expense);

    expect($result)->toBe(1);
    expect($transaction->fresh()->expense_id)->toBe($expense->id);
});

