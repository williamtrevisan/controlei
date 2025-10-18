<?php

use App\Actions\ClassifyExpenses;
use App\Actions\ClassifyIncomeSources;
use App\Actions\ClassifyTransactions;
use App\Jobs\ReclassifyAllTransactions;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

it('authenticates the user before reclassifying', function () {
    $this->mock(ClassifyTransactions::class)
        ->expects('execute');

    $this->mock(ClassifyExpenses::class)
        ->expects('execute');

    $this->mock(ClassifyIncomeSources::class)
        ->expects('execute');

    ReclassifyAllTransactions::dispatch($user = User::factory()->create());

    expect(auth()->user()->id)
        ->toBe($user->id);
});

it('calls all three classification actions in order', function () {
    $this->mock(ClassifyTransactions::class)
        ->expects('execute')
        ->ordered();

    $this->mock(ClassifyExpenses::class)
        ->expects('execute')
        ->ordered();

    $this->mock(ClassifyIncomeSources::class)
        ->expects('execute')
        ->ordered();

    ReclassifyAllTransactions::dispatch(User::factory()->create());
});

it('has correct tags for queue monitoring', function () {
    expect((new ReclassifyAllTransactions($user = User::factory()->create()))->tags())
        ->toBe([
            'reclassify-all-transactions',
            "user:$user->id",
        ]);
});

it('should queue', function () {
    expect(new ReclassifyAllTransactions(User::factory()->create()))
        ->toBeInstanceOf(ShouldQueue::class);
});

