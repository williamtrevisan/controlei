<?php

namespace App\Jobs;

use App\Actions\CategorizeManyTransactions;
use App\Actions\ClassifyExpenses;
use App\Actions\ClassifyIncomeSources;
use App\Actions\ClassifyTransactions;
use App\Actions\GetAllUserTransactions;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ReclassifyAllTransactions implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public ?int $maxExceptions = 1;

    public int $timeout = 0;

    public function __construct(
        private readonly User $user,
    ) {}

    public function handle(
        ClassifyTransactions $classifyTransactions,
        ClassifyExpenses $classifyExpenses,
        ClassifyIncomeSources $classifyIncomeSources,
        CategorizeManyTransactions $categorizeManyTransactions,
        GetAllUserTransactions $getAllTransactions
    ): void {
        auth()->setUser($this->user);

        DB::transaction(function () use (
            $classifyTransactions,
            $classifyExpenses,
            $classifyIncomeSources,
            $categorizeManyTransactions,
            $getAllTransactions
        ): void {
            $classifyTransactions->execute();
            $classifyExpenses->execute();
            $classifyIncomeSources->execute();
        });

        $categorizeManyTransactions->execute($getAllTransactions->execute());
    }

    public function tags(): array
    {
        return [
            'reclassify-all-transactions',
            "user:{$this->user->id}",
        ];
    }
}
