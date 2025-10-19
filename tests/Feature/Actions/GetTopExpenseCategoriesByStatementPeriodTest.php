<?php

use App\Actions\GetTopExpenseCategoriesByStatementPeriod;
use App\Models\Category;
use App\Models\Statement;
use App\Models\Transaction;
use App\Models\User;
use App\ValueObjects\StatementPeriod;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('returns top N categories with percentages', function () {
    $period = (new StatementPeriod())->current();
    
    $categories = Category::factory()->count(7)->create();
    
    $statement = Statement::factory()->create([
        'card_id' => null,
        'account_id' => $this->user->account->id,
        'period' => $period->value(),
    ]);

    // Create transactions with varying amounts
    foreach ($categories as $index => $category) {
        Transaction::factory()->create([
            'account_id' => $this->user->account->id,
            'category_id' => $category->id,
            'statement_id' => $statement->id,
            'direction' => 'outflow',
            'kind' => 'purchase',
            'amount' => money()->of(100 * ($index + 1)), // 100, 200, 300, 400, 500, 600, 700
        ]);
    }

    $action = app()->make(GetTopExpenseCategoriesByStatementPeriod::class);
    $result = $action->execute($period, limit: 5);

    expect($result)->toHaveCount(5);
    
    // Should return top 5, ordered by amount descending
    $firstCategory = $result->first();
    expect($firstCategory)
        ->toHaveKey('percentage')
        ->and($firstCategory['total']->getMinorAmount()->toInt())->toBe(70000); // 700

    $lastOfTop5 = $result->last();
    expect($lastOfTop5['total']->getMinorAmount()->toInt())->toBe(30000); // 300
});

it('calculates correct percentages', function () {
    $period = (new StatementPeriod())->current();
    
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    
    $statement = Statement::factory()->create([
        'card_id' => null,
        'account_id' => $this->user->account->id,
        'period' => $period->value(),
    ]);

    // Category 1: 200 (66.67%)
    Transaction::factory()->create([
        'account_id' => $this->user->account->id,
        'category_id' => $category1->id,
        'statement_id' => $statement->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => money()->of(200),
    ]);

    // Category 2: 100 (33.33%)
    Transaction::factory()->create([
        'account_id' => $this->user->account->id,
        'category_id' => $category2->id,
        'statement_id' => $statement->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => money()->of(100),
    ]);

    $action = app()->make(GetTopExpenseCategoriesByStatementPeriod::class);
    $result = $action->execute($period);

    $first = $result->first();
    $second = $result->last();

    expect($first['percentage'])->toBeGreaterThan(66)
        ->and($first['percentage'])->toBeLessThan(67)
        ->and($second['percentage'])->toBeGreaterThan(33)
        ->and($second['percentage'])->toBeLessThan(34);
});

it('respects limit parameter', function () {
    $period = (new StatementPeriod())->current();
    
    $categories = Category::factory()->count(10)->create();
    
    $statement = Statement::factory()->create([
        'card_id' => null,
        'account_id' => $this->user->account->id,
        'period' => $period->value(),
    ]);

    foreach ($categories as $category) {
        Transaction::factory()->create([
            'account_id' => $this->user->account->id,
            'category_id' => $category->id,
            'statement_id' => $statement->id,
            'direction' => 'outflow',
            'kind' => 'purchase',
            'amount' => money()->of(100),
        ]);
    }

    $action = app()->make(GetTopExpenseCategoriesByStatementPeriod::class);
    
    expect($action->execute($period, limit: 3))->toHaveCount(3);
    expect($action->execute($period, limit: 5))->toHaveCount(5);
    expect($action->execute($period, limit: 10))->toHaveCount(10);
});

it('returns empty collection when no expenses exist', function () {
    $period = (new StatementPeriod())->current();
    
    $action = app()->make(GetTopExpenseCategoriesByStatementPeriod::class);
    $result = $action->execute($period);

    expect($result)->toBeEmpty();
});

it('defaults to limit of 5', function () {
    $period = (new StatementPeriod())->current();
    
    $categories = Category::factory()->count(10)->create();
    
    $statement = Statement::factory()->create([
        'card_id' => null,
        'account_id' => $this->user->account->id,
        'period' => $period->value(),
    ]);

    foreach ($categories as $category) {
        Transaction::factory()->create([
            'account_id' => $this->user->account->id,
            'category_id' => $category->id,
            'statement_id' => $statement->id,
            'direction' => 'outflow',
            'kind' => 'purchase',
            'amount' => money()->of(100),
        ]);
    }

    $action = app()->make(GetTopExpenseCategoriesByStatementPeriod::class);
    $result = $action->execute($period); // No limit parameter

    expect($result)->toHaveCount(5);
});

