<?php

namespace App\Jobs;

use App\Actions\CreateManyTransactions;
use App\Models\Synchronization;
use Carbon\CarbonInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Throwable;

class SynchronizeTransactions implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public $failOnTimeout = true;

    public int $tries = 1;

    public ?int $maxExceptions = 1;

    public function __construct(
        private readonly Synchronization $synchronization,
        private readonly LazyCollection $transactions,
    ) {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("synchronization{$this->synchronization->getKey()}"))
                ->expireAfter(600),
        ];
    }

    public function handle(CreateManyTransactions $createManyTransactions): void
    {
        auth()->setUser($this->synchronization->user);

        try {
            $createManyTransactions->execute($this->transactions);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function tags(): array
    {
        return [
            "synchronization{$this->synchronization->getKey()}",
        ];
    }
}
