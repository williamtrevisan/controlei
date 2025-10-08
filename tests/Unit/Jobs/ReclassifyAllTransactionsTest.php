<?php

use App\Actions\ClassifyExpenses;
use App\Actions\ClassifyIncomeSources;
use App\Actions\ClassifyTransactions;
use App\Jobs\ReclassifyAllTransactions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('authenticates the user before reclassifying', function () {
    $user = User::factory()->create();

    $job = new ReclassifyAllTransactions($user);

    $classifyTransactions = $this->mock(ClassifyTransactions::class);
    $classifyExpenses = $this->mock(ClassifyExpenses::class);
    $classifyIncomeSources = $this->mock(ClassifyIncomeSources::class);

    $classifyTransactions->shouldReceive('execute')->once();
    $classifyExpenses->shouldReceive('execute')->once();
    $classifyIncomeSources->shouldReceive('execute')->once();

    $job->handle($classifyTransactions, $classifyExpenses, $classifyIncomeSources);

    expect(auth()->user()->id)->toBe($user->id);
});

it('calls all three classification actions in order', function () {
    $user = User::factory()->create();

    $job = new ReclassifyAllTransactions($user);

    $classifyTransactions = $this->mock(ClassifyTransactions::class);
    $classifyExpenses = $this->mock(ClassifyExpenses::class);
    $classifyIncomeSources = $this->mock(ClassifyIncomeSources::class);

    $classifyTransactions->shouldReceive('execute')->once()->ordered();
    $classifyExpenses->shouldReceive('execute')->once()->ordered();
    $classifyIncomeSources->shouldReceive('execute')->once()->ordered();

    $job->handle($classifyTransactions, $classifyExpenses, $classifyIncomeSources);
});

it('has correct tags for queue monitoring', function () {
    $user = User::factory()->create();

    $job = new ReclassifyAllTransactions($user);

    expect($job->tags())->toBe([
        'reclassify-all-transactions',
        "user:{$user->id}",
    ]);
});

it('is batchable', function () {
    $user = User::factory()->create();

    $job = new ReclassifyAllTransactions($user);

    $reflection = new ReflectionClass($job);
    $traits = $reflection->getTraitNames();

    expect($traits)->toContain('Illuminate\Bus\Batchable');
});

it('should queue', function () {
    $user = User::factory()->create();

    $job = new ReclassifyAllTransactions($user);

    expect($job)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});

