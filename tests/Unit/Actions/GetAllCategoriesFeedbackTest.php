<?php

use App\Actions\GetAllCategoriesFeedback;
use App\Models\Category;
use App\Models\TransactionCategoryFeedback;

it('returns all category feedback records', function () {
    TransactionCategoryFeedback::factory(count: 5)
        ->for(Category::factory()->create())
        ->createQuietly();

    $feedback = app()->make(GetAllCategoriesFeedback::class)->execute();

    expect($feedback)
        ->toHaveCount(5)
        ->each->toBeInstanceOf(TransactionCategoryFeedback::class);
});

it('returns an empty collection when no feedback exists', function () {
    $feedback = app()->make(GetAllCategoriesFeedback::class)->execute();

    expect($feedback)
        ->toBeEmpty();
});
