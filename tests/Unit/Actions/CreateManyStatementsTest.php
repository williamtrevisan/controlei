<?php

use App\Actions\CreateManyStatements;
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

it('creates new statements', function () {
    $statement = factory(StatementDataFactory::class)
        ->create(attributes: [
            'accountId' => $this->account->id,
            'cardId' => $this->card->id,
            'period' => '2025-10',
        ]);

    app()->make(CreateManyStatements::class)
        ->execute(collect([$statement]));

    $this->assertDatabaseHas(Statement::class, [
        'account_id' => $this->account->id,
        'card_id' => $this->card->id,
        'period' => '2025-10',
    ]);
});

it('creates multiple statements', function () {
    $statements = factory(StatementDataFactory::class)
        ->sequence([
            ['period' => '2025-10'],
            ['period' => '2025-11'],
        ])
        ->create(count: 2, attributes: [
            'accountId' => $this->account->id,
            'cardId' => $this->card->id,
        ]);

    app()->make(CreateManyStatements::class)
        ->execute($statements);

    $this->assertDatabaseCount(Statement::class, 2);
});
