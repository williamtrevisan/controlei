<?php

use App\Actions\CreateCategoryFeedback;
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

it('creates category feedback for a transaction', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    $feedback = app()->make(CreateCategoryFeedback::class)
        ->execute($transaction, $this->category->id);

    $expectedInstallments = $transaction->total_installments > 1 ? $transaction->total_installments : null;

    expect($feedback)
        ->toBeInstanceOf(TransactionCategoryFeedback::class)
        ->transaction_id->toBe($transaction->id)
        ->description->toBe($transaction->description)
        ->direction->toBe($transaction->direction->value)
        ->amount->toBe($transaction->amount->getAmount()->toFloat())
        ->kind->toBe($transaction->kind->value)
        ->payment_method->toBe($transaction->payment_method->value)
        ->total_installments->toBe($expectedInstallments)
        ->category_id->toBe($this->category->id);
});
