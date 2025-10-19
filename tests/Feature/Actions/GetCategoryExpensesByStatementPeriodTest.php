<?php

use App\Actions\GetCategoryExpensesByStatementPeriod;
use App\Models\Category;
use App\Models\Statement;
use App\Models\Transaction;
use App\Models\User;
use App\ValueObjects\StatementPeriod;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('aggregates expenses by category for a statement period', function () {
    $period = (new StatementPeriod())->current();
    
    $foodCategory = Category::factory()->create(['description' => 'Alimentação']);
    $transportCategory = Category::factory()->create(['description' => 'Transporte']);
    
    $statement = Statement::factory()->create([
        'card_id' => null,
        'account_id' => $this->user->account->id,
        'period' => $period->value(),
    ]);

    // Create transactions for food category
    Transaction::factory()->count(3)->create([
        'account_id' => $this->user->account->id,
        'category_id' => $foodCategory->id,
        'statement_id' => $statement->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => money()->of(100),
    ]);

    // Create transactions for transport category
    Transaction::factory()->count(2)->create([
        'account_id' => $this->user->account->id,
        'category_id' => $transportCategory->id,
        'statement_id' => $statement->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => money()->of(50),
    ]);

    $action = app()->make(GetCategoryExpensesByStatementPeriod::class);
    $result = $action->execute($period);

    expect($result)->toHaveCount(2);
    
    $foodResult = $result->firstWhere('category_id', $foodCategory->id);
    expect($foodResult)
        ->toBeArray()
        ->and($foodResult['category_name'])->toBe('Alimentação')
        ->and($foodResult['total']->getMinorAmount()->toInt())->toBe(30000) // 3 * 100 = 300
        ->and($foodResult['count'])->toBe(3);

    $transportResult = $result->firstWhere('category_id', $transportCategory->id);
    expect($transportResult)
        ->toBeArray()
        ->and($transportResult['category_name'])->toBe('Transporte')
        ->and($transportResult['total']->getMinorAmount()->toInt())->toBe(10000) // 2 * 50 = 100
        ->and($transportResult['count'])->toBe(2);
});

it('returns results sorted by total amount descending', function () {
    $period = (new StatementPeriod())->current();
    
    $category1 = Category::factory()->create(['description' => 'Categoria 1']);
    $category2 = Category::factory()->create(['description' => 'Categoria 2']);
    
    $statement = Statement::factory()->create([
        'card_id' => null,
        'account_id' => $this->user->account->id,
        'period' => $period->value(),
    ]);

    Transaction::factory()->create([
        'account_id' => $this->user->account->id,
        'category_id' => $category1->id,
        'statement_id' => $statement->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => money()->of(50),
    ]);

    Transaction::factory()->create([
        'account_id' => $this->user->account->id,
        'category_id' => $category2->id,
        'statement_id' => $statement->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => money()->of(200),
    ]);

    $action = app()->make(GetCategoryExpensesByStatementPeriod::class);
    $result = $action->execute($period);

    expect($result->first()['category_id'])->toBe($category2->id)
        ->and($result->last()['category_id'])->toBe($category1->id);
});

it('handles transactions without category', function () {
    $period = (new StatementPeriod())->current();
    
    $statement = Statement::factory()->create([
        'card_id' => null,
        'account_id' => $this->user->account->id,
        'period' => $period->value(),
    ]);

    Transaction::factory()->create([
        'account_id' => $this->user->account->id,
        'category_id' => null,
        'statement_id' => $statement->id,
        'direction' => 'outflow',
        'kind' => 'purchase',
        'amount' => money()->of(100),
    ]);

    $action = app()->make(GetCategoryExpensesByStatementPeriod::class);
    $result = $action->execute($period);

    expect($result)->toHaveCount(1);
    
    $uncategorized = $result->first();
    expect($uncategorized['category_name'])->toBe('Sem categoria')
        ->and($uncategorized['category_id'])->toBeNull();
});

it('returns empty collection when no expenses exist for period', function () {
    $period = (new StatementPeriod())->current();
    
    $action = app()->make(GetCategoryExpensesByStatementPeriod::class);
    $result = $action->execute($period);

    expect($result)->toBeEmpty();
});

