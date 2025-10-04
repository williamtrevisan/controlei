<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\DataTransferObjects\SynchronizationData;
use Closure;

class ExtractAccountTransactions
{
    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $transactions = $data->bank
            ->account()
            ->transactions()
            ->between(now()->startOfYear(), now())
            ->lazy();

        return $next($data->withAccountTransactions($transactions));
    }
}
