<?php

use App\Actions\GetAllUserMonthlyIncomeSources;
use App\Enums\IncomeFrequency;
use App\Models\IncomeSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs($this->user = User::factory()->create());
});

it('returns all active monthly income sources for the user', function () {
    IncomeSource::factory(count: 2)
        ->monthly()
        ->for($this->user)
        ->create();

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)
        ->execute();

    expect($incomeSources)
        ->toHaveCount(2);
});

it('does not return annually income sources', function () {
    IncomeSource::factory()
        ->monthly()
        ->for($this->user)
        ->create();

    IncomeSource::factory()
        ->annually()
        ->for($this->user)
        ->create();

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)
        ->execute();

    expect($incomeSources)
        ->toHaveCount(1)
        ->first()->frequency->toBe(IncomeFrequency::Monthly);
});

it('does not return occasionally income sources', function () {
    IncomeSource::factory()
        ->monthly()
        ->for($this->user)
        ->create();

    IncomeSource::factory()
        ->occasionally()
        ->for($this->user)
        ->create();

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)
        ->execute();

    expect($incomeSources)
        ->toHaveCount(1)
        ->first()->frequency->toBe(IncomeFrequency::Monthly);
});

it('does not return inactive income sources', function () {
    IncomeSource::factory()
        ->monthly()
        ->sequence(
            ['active' => true],
            ['active' => false],
        )
        ->for($this->user)
        ->create();

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)
        ->execute();

    expect($incomeSources)
        ->toHaveCount(1)
        ->first()->active->toBeTrue();
});

it('only returns income sources for the authenticated user', function () {
    IncomeSource::factory()
        ->monthly()
        ->for($this->user)
        ->create();

    IncomeSource::factory()
        ->monthly()
        ->for(User::factory()->create())
        ->create();

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)
        ->execute();

    expect($incomeSources)
        ->toHaveCount(1)
        ->first()->user_id->toBe($this->user->id);
});

it('returns empty collection when user has no monthly income sources', function () {
    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)
        ->execute();

    expect($incomeSources)
        ->toBeEmpty();
});
