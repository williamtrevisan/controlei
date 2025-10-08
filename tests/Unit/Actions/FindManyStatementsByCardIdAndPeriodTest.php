<?php

use App\Actions\FindManyStatementsByCardIdAndPeriod;
use App\Models\Account;
use App\Models\Card;
use App\Models\Statement;
use App\Models\User;
use Tests\Support\Factories\StatementDataFactory;

beforeEach(function () {
    $this->card = Card::factory()
        ->for(
            $this->account = Account::factory()
                ->for($user = User::factory()->create())
                ->create()
        )
        ->create();

    $this->actingAs($user);
});

it('returns empty collection when no statements data provided', function () {
    $data = app()->make(FindManyStatementsByCardIdAndPeriod::class)
        ->execute(collect());

    expect($data)
        ->toBeEmpty();
});

it('finds statements by card id and period', function () {
    $statement = Statement::factory()
        ->for($this->account)
        ->for($this->card)
        ->create(['period' => '2024-10']);

    $data = app()->make(FindManyStatementsByCardIdAndPeriod::class)
        ->execute(collect([
            factory(StatementDataFactory::class)
                ->create(attributes: [
                    'accountId' => $this->account->id,
                    'cardId' => $this->card->id,
                    'period' => '2024-10',
                ])
        ]));

    expect($data)
        ->toHaveCount(1)
        ->first()->id->toBe($statement->id);
});

it('finds multiple statements matching different card id and period combinations', function () {
    $card = Card::factory()
        ->for($this->account)
        ->create();

    $statements = Statement::factory(count: 2)
        ->for($this->account)
        ->for($this->card)
        ->sequence(
            ['period' => '2024-10'],
            ['period' => '2024-11'],
        )
        ->create();

    $data = app()->make(FindManyStatementsByCardIdAndPeriod::class)
        ->execute(
            factory(StatementDataFactory::class)
                ->sequence([
                    [
                        'accountId' => $this->account->id,
                        'cardId' => $this->card->id,
                        'period' => '2024-10',
                    ],
                    [
                        'accountId' => $this->account->id,
                        'cardId' => $card->id,
                        'period' => '2024-11',
                    ],
                ])
                ->create(count: 2)
        );

    expect($data)
        ->toHaveCount(2)
        ->pluck('id')->toContain($statements->first()->id, $statements->last()->id);
});

