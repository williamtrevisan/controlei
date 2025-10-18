<?php

use App\Actions\UpdateCategoryFeedback;
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

it('updates category feedback with new category and transaction context', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    $feedback = TransactionCategoryFeedback::factory()
        ->for($transaction)
        ->for(Category::factory()->create())
        ->create();

    $updated = app()->make(UpdateCategoryFeedback::class)
        ->execute($feedback, $this->category->id);

    expect($updated)
        ->toBeTrue();

    $expectedInstallments = $transaction->total_installments > 1 ? $transaction->total_installments : null;
    expect($feedback->fresh())
        ->category_id->toBe($this->category->id)
        ->description->toBe($transaction->description)
        ->direction->toBe($transaction->direction->value)
        ->amount->toBe($transaction->amount->getAmount()->toFloat())
        ->kind->toBe($transaction->kind->value)
        ->payment_method->toBe($transaction->payment_method->value)
        ->total_installments->toBe($expectedInstallments);
});
