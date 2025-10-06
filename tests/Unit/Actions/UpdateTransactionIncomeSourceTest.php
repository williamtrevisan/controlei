<?php

use App\Actions\UpdateTransactionIncomeSource;
use App\Models\Account;
use App\Models\IncomeSource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

it('batch updates multiple transactions with income source id', function () {
    $incomeSource = IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Salary',
    ]);

    $transaction1 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'income_source_id' => null,
    ]);

    $transaction2 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'income_source_id' => null,
    ]);

    $result = app()->make(UpdateTransactionIncomeSource::class)
        ->execute(collect([$transaction1, $transaction2]), $incomeSource);

    expect($result)->toBe(2);
    expect($transaction1->fresh()->income_source_id)->toBe($incomeSource->id);
    expect($transaction2->fresh()->income_source_id)->toBe($incomeSource->id);
});

it('batch updates transaction income source from one to another', function () {
    $oldIncomeSource = IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Old Job',
    ]);

    $newIncomeSource = IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'New Job',
    ]);

    $transaction1 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'income_source_id' => $oldIncomeSource->id,
    ]);

    $transaction2 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'income_source_id' => $oldIncomeSource->id,
    ]);

    $result = app()->make(UpdateTransactionIncomeSource::class)
        ->execute(collect([$transaction1, $transaction2]), $newIncomeSource);

    expect($result)->toBe(2);
    expect($transaction1->fresh()->income_source_id)->toBe($newIncomeSource->id);
    expect($transaction2->fresh()->income_source_id)->toBe($newIncomeSource->id);
});

it('removes income source from multiple transactions when null is passed', function () {
    $incomeSource = IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Income',
    ]);

    $transaction1 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'income_source_id' => $incomeSource->id,
    ]);

    $transaction2 = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'income_source_id' => $incomeSource->id,
    ]);

    $result = app()->make(UpdateTransactionIncomeSource::class)
        ->execute(collect([$transaction1, $transaction2]), null);

    expect($result)->toBe(2);
    expect($transaction1->fresh()->income_source_id)->toBeNull();
    expect($transaction2->fresh()->income_source_id)->toBeNull();
});

it('returns number of updated transactions', function () {
    $incomeSource = IncomeSource::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $transactions = Transaction::factory()->count(5)->create([
        'account_id' => $this->account->id,
    ]);

    $result = app()->make(UpdateTransactionIncomeSource::class)
        ->execute($transactions, $incomeSource);

    expect($result)->toBe(5);
});

it('returns 0 when no transactions provided', function () {
    $incomeSource = IncomeSource::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $result = app()->make(UpdateTransactionIncomeSource::class)
        ->execute(collect(), $incomeSource);

    expect($result)->toBe(0);
});

it('handles single transaction batch update', function () {
    $incomeSource = IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Income',
    ]);

    $transaction = Transaction::factory()->create([
        'account_id' => $this->account->id,
        'income_source_id' => null,
    ]);

    $result = app()->make(UpdateTransactionIncomeSource::class)
        ->execute(collect([$transaction]), $incomeSource);

    expect($result)->toBe(1);
    expect($transaction->fresh()->income_source_id)->toBe($incomeSource->id);
});

