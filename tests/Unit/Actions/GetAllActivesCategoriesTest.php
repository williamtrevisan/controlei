<?php

use App\Actions\GetAllActivesCategories;
use App\Models\Category;

it('returns only active categories', function () {
    Category::factory(count: 3)
        ->create(['active' => true]);
        
    Category::factory(count: 2)
        ->create(['active' => false]);

    $categories = app()->make(GetAllActivesCategories::class)->execute();

    expect($categories)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Category::class);
});

it('returns an empty collection when no active categories exist', function () {
    Category::factory()
        ->count(3)
        ->create(['active' => false]);

    $categories = app()->make(GetAllActivesCategories::class)->execute();

    expect($categories)
        ->toBeEmpty();
});

it('returns all active categories with correct attributes', function () {
    Category::factory()->create();

    Category::factory()
        ->create(['active' => false]);

    $categories = app()->make(GetAllActivesCategories::class)->execute();

    expect($categories)
        ->toHaveCount(1)
        ->first()->active->toBeTrue();
});
