<?php

use App\Actions\GetAllUserMonthlyIncomeSources;
use App\Enums\IncomeFrequency;
use App\Models\IncomeSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('returns all active monthly income sources for the user', function () {
    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Salary',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 500000,
    ]);

    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Freelance',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 200000,
    ]);

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)->execute();

    expect($incomeSources)->toHaveCount(2);
});

it('does not return annually income sources', function () {
    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Monthly Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 500000,
    ]);

    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => '13th Salary',
        'frequency' => IncomeFrequency::Annually,
        'active' => true,
        'average_amount' => 500000,
    ]);

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)->execute();

    expect($incomeSources)->toHaveCount(1);
    expect($incomeSources->first()->name)->toBe('Monthly Income');
});

it('does not return occasionally income sources', function () {
    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Monthly Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 500000,
    ]);

    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Tax Return',
        'frequency' => IncomeFrequency::Occasionally,
        'active' => true,
        'average_amount' => 100000,
    ]);

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)->execute();

    expect($incomeSources)->toHaveCount(1);
    expect($incomeSources->first()->name)->toBe('Monthly Income');
});

it('does not return inactive income sources', function () {
    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Active Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 500000,
    ]);

    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Inactive Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => false,
        'average_amount' => 300000,
    ]);

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)->execute();

    expect($incomeSources)->toHaveCount(1);
    expect($incomeSources->first()->name)->toBe('Active Income');
});

it('does not return income sources with null average amount', function () {
    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'With Amount',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 500000,
    ]);

    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Without Amount',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => null,
    ]);

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)->execute();

    expect($incomeSources)->toHaveCount(1);
    expect($incomeSources->first()->name)->toBe('With Amount');
});

it('only returns income sources for the authenticated user', function () {
    $otherUser = User::factory()->create();

    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'My Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 500000,
    ]);

    IncomeSource::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Other Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 600000,
    ]);

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)->execute();

    expect($incomeSources)->toHaveCount(1);
    expect($incomeSources->first()->name)->toBe('My Income');
});

it('returns empty collection when user has no monthly income sources', function () {
    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)->execute();

    expect($incomeSources)->toBeEmpty();
});

it('returns income sources ordered by name', function () {
    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Zebra Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 100000,
    ]);

    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Apple Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 200000,
    ]);

    IncomeSource::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Mango Income',
        'frequency' => IncomeFrequency::Monthly,
        'active' => true,
        'average_amount' => 300000,
    ]);

    $incomeSources = app()->make(GetAllUserMonthlyIncomeSources::class)->execute();

    expect($incomeSources)->toHaveCount(3);
    expect($incomeSources->pluck('name')->toArray())->toBe([
        'Apple Income',
        'Mango Income',
        'Zebra Income',
    ]);
});

