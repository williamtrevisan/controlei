<?php

use App\Actions\GetAllUserStatements;
use App\Models\Account;
use App\Models\Card;
use App\Models\Statement;
use App\Models\User;

it('returns all statements for authenticated user', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()->for($user)->create();
    $card = Card::factory()->for($account)->create();

    $statements = Statement::factory(count: 2)
        ->for($account)
        ->for($card)
        ->sequence(
            ['period' => '2025-01'],
            ['period' => '2025-02']
        )
        ->create();

    $userStatements = app()->make(GetAllUserStatements::class)
        ->execute();

    expect($userStatements)
        ->toHaveCount(2)
        ->each(fn ($statement) => $statement->toBeInstanceOf(Statement::class))
        ->and($userStatements)
            ->pluck('id')->toContain($statements->first()->id, $statements->last()->id);
});

it('returns only statements for the authenticated user', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()->for($user)->create();
    $card = Card::factory()->for($account)->create();

    $statement = Statement::factory()
        ->for($account)
        ->for($card)
        ->create(['period' => '2025-01']);

    Statement::factory()
        ->create(['period' => '2025-01']);

    $userStatements = app()->make(GetAllUserStatements::class)
        ->execute();

    expect($userStatements)
        ->toHaveCount(1)
        ->first()->id->toBe($statement->id);
});

it('returns empty collection when no statements exist', function () {
    $statements = app()->make(GetAllUserStatements::class)
        ->execute();

    expect($statements)
        ->toBeEmpty();
});

