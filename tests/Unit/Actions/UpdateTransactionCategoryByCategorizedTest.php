<?php

use App\Actions\UpdateTransactionCategoryByCategorized;
use App\DataTransferObjects\CategorizedTransactionData;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->account = Account::factory()
        ->for($this->user = User::factory()->create())
        ->create();

    $this->actingAs($this->user);

    $this->category = Category::factory()->create();
});

it('updates single transaction category from categorized data', function () {
    $transaction = Transaction::factory()
        ->for($this->account)
        ->createQuietly();

    $categorizedData = new CategorizedTransactionData(
        id: $transaction->id,
        categoryId: $this->category->id
    );

    app()->make(UpdateTransactionCategoryByCategorized::class)
        ->execute(collect([$categorizedData]));

    expect($transaction)
        ->fresh()->category_id->toBe($this->category->id);
});

it('updates multiple transactions from categorized data', function () {
    $transactions = Transaction::factory(count: 2)
        ->for($this->account)
        ->createQuietly();

    $category = Category::factory()->create();

    $categorizedData = collect([
        new CategorizedTransactionData($transactions->first()->id, $this->category->id),
        new CategorizedTransactionData($transactions->last()->id, $category->id),
    ]);

    app()->make(UpdateTransactionCategoryByCategorized::class)
        ->execute($categorizedData);

    expect($transactions)
        ->first()->fresh()->category_id->toBe($this->category->id)
        ->last()->fresh()->category_id->toBe($category->id);
});
