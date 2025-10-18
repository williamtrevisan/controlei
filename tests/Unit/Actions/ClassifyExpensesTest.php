<?php

use App\Actions\ClassifyExpenses;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->account = Account::factory()
        ->for($this->user = User::factory()->create())
        ->create();

    $this->actingAs($this->user);
});

it('assigns expenses to transactions based on matcher regex', function () {
    $expense = Expense::factory()
        ->for($this->user)
        ->create([
            'matcher_regex' => '/netflix/i',
        ]);

    $transaction = Transaction::factory()
        ->expense()
        ->for($this->account)
        ->createQuietly([
            'description' => 'Netflix Subscription'
        ]);

    app()->make(ClassifyExpenses::class)
        ->execute();

    expect($transaction->fresh())
        ->expense_id->toBe($expense->id);
});

it('does not assign expenses to inflow transactions', function () {
    Expense::factory()->create();

    $transaction = Transaction::factory()
        ->inflow()
        ->for($this->account)
        ->createQuietly();

    app()->make(ClassifyExpenses::class)
        ->execute();

    expect($transaction)
        ->fresh()->expense_id->toBeNull();
});

it('does not assign expenses when matcher regex is empty', function () {
    Expense::factory()
        ->for($this->user)
        ->create();

    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    app()->make(ClassifyExpenses::class)
        ->execute();

    expect($transaction)
        ->fresh()->expense_id->toBeNull();
});
