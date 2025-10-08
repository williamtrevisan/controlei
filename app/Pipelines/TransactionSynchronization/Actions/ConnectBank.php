<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\DataTransferObjects\SynchronizationData;
use Closure;

class ConnectBank
{
    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $bank = banklink()
            ->authenticate($data->token);

        return $next($data->withBank($bank));
    }
}
