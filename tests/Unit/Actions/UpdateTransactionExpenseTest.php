<?php

use App\Actions\UpdateTransactionExpense;
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

it('updates transaction with expense id', function () {
    $expense = Expense::factory()
        ->for($this->user)
        ->create();

    $transaction = Transaction::factory()
        ->has(Expense::factory())
        ->for($this->account)
        ->createQuietly();

    app()->make(UpdateTransactionExpense::class)
        ->execute($transaction, $expense);

    expect($transaction)
        ->fresh()->expense_id->toBe($expense->id);
});
