<?php

use App\Actions\UpdateTransactionIncomeSource;
use App\Models\Account;
use App\Models\IncomeSource;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->account = Account::factory()
        ->for($this->user = User::factory()->create())
        ->create();

    $this->actingAs($this->user);
});

it('updates transaction with income source id', function () {
    $incomeSource = IncomeSource::factory()
        ->for($this->user)
        ->create();

    $transaction = Transaction::factory()
        ->has(IncomeSource::factory())
        ->for($this->account)
        ->createQuietly();

    app()->make(UpdateTransactionIncomeSource::class)
        ->execute($transaction, $incomeSource);

    expect($transaction)
        ->fresh()->income_source_id->toBe($incomeSource->id);
});
