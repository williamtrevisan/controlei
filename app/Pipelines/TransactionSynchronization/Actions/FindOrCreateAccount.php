<?php

namespace App\Pipelines\TransactionSynchronization\Actions;

use App\DataTransferObjects\SynchronizationData;
use Closure;

class FindOrCreateAccount
{
    public function __construct(
        private readonly \App\Actions\FindOrCreateAccount $findOrCreateAccount,
    ) {
    }

    public function handle(SynchronizationData $data, Closure $next): SynchronizationData
    {
        $account = $this->findOrCreateAccount
            ->execute($data->bank->account());

        return $next($data->withAccount($account));
    }
}
