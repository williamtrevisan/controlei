<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\DataTransferObjects\SynchronizationData;
use Closure;

readonly class UpdateAccountBalance
{
    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $data->account->update([
            'balance' => $data->bank->account()->balance(),
        ]);

        return $next($data);
    }
}

