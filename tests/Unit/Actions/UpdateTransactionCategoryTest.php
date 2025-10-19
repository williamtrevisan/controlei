<?php

use App\Actions\UpdateTransactionCategory;
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

it('updates transaction category', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    $updated = app()->make(UpdateTransactionCategory::class)
        ->execute($transaction, $this->category->id);

    expect($updated)
        ->toBeTrue()
        ->and($transaction->fresh()->category_id)->toBe($this->category->id);
});

it('creates feedback when none exists', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    app()->make(UpdateTransactionCategory::class)
        ->execute($transaction, $this->category->id);

    $this->assertDatabaseHas(TransactionCategoryFeedback::class, [
        'transaction_id' => $transaction->id,
        'description' => $transaction->description,
        'category_id' => $this->category->id,
    ]);
});

it('updates existing feedback when it exists', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly([
            'category_id' => $this->category->id,
        ]);

    $feedback = TransactionCategoryFeedback::factory()->create([
        'transaction_id' => $transaction->id,
        'description' => $transaction->description,
        'category_id' => Category::factory()->create()->id,
    ]);

    $category = Category::factory()->create();

    app()->make(UpdateTransactionCategory::class)
        ->execute($transaction, $category->id);

    expect($feedback)
        ->fresh()->category_id->toBe($category->id);
});

it('updates both transaction and feedback atomically', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    app()->make(UpdateTransactionCategory::class)
        ->execute($transaction, $this->category->id);

    $this->assertDatabaseHas(TransactionCategoryFeedback::class, [
        'transaction_id' => $transaction->id,
        'category_id' => $this->category->id,
    ]);

    expect($transaction)
        ->fresh()->category_id->toBe($this->category->id);
});
