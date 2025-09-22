<?php

namespace App\Jobs;

use App\Actions\GetAllBankTransactions;
use App\Models\Synchronization;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\LazyCollection;
use Throwable;

class FetchAndSynchronizeTransactions implements ShouldQueue
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
        private readonly string $token,
    ) {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("synchronization{$this->synchronization->getKey()}"))
                ->expireAfter(600),
        ];
    }

    public function handle(GetAllBankTransactions $getAllBankTransactions): void
    {
        auth()->setUser($this->synchronization->user);

        try {
            $jobs = $getAllBankTransactions
                ->execute($this->token)
                ->chunk(100)
                ->map(fn (LazyCollection $chunk) => new SynchronizeTransactions($this->synchronization, $chunk));
            if ($jobs->isEmpty()) {
                return;
            }

            $this->batch()
                ->add($jobs->all());
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
