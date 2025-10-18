<?php

use App\Actions\GetCategoryFeedbackByTransactionId;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionCategoryFeedback;
use App\Models\User;

beforeEach(function () {
    $this->account = Account::factory()
        ->for($this->user = User::factory()->create())
        ->create();

    $this->actingAs($this->user);

    $this->category = Category::factory()->create();
});

it('retrieves existing feedback by transaction id', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    TransactionCategoryFeedback::factory()
        ->for($transaction)
        ->for($this->category)
        ->create();

    $feedback = app()->make(GetCategoryFeedbackByTransactionId::class)
        ->execute($transaction->id);

    expect($feedback)
        ->toBeInstanceOf(TransactionCategoryFeedback::class)
        ->transaction_id->toBe($transaction->id)
        ->category_id->toBe($this->category->id);
});

it('returns null when no feedback exists for transaction', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    $feedback = app()->make(GetCategoryFeedbackByTransactionId::class)
        ->execute($transaction->id);

    expect($feedback)
        ->toBeNull();
});

it('returns null for non-existent transaction id', function () {
    $feedback = app()->make(GetCategoryFeedbackByTransactionId::class)
        ->execute('non-existent-id');

    expect($feedback)
        ->toBeNull();
});
