<?php

namespace App\Listeners;

use App\Actions\CategorizeManyTransactions;
use App\Actions\GetAllUserTransactions;
use App\Events\SynchronizationCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CategorizeTransactions implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly CategorizeManyTransactions $categorizeManyTransactions,
        private readonly GetAllUserTransactions $getAllUserTransactions,
    ) {}

    public function handle(SynchronizationCompleted $event): void
    {
        auth()->setUser($event->synchronization->user);

        $this->categorizeManyTransactions->execute($this->getAllUserTransactions->execute());
    }
}
