<?php

use App\DataTransferObjects\SynchronizationData;
use App\Pipelines\TransactionSynchronization\Actions\ConnectBank;
use Banklink\Contracts\Bank;

it('connects to bank', function () {
    $token = '::fake::';

    $this->mock('banklink')
        ->expects('authenticate')
        ->with($token)
        ->andReturn($bank = $this->mock(Bank::class));

    $data = app()->make(ConnectBank::class)
        ->handle(
            SynchronizationData::from($token),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    expect($data)
        ->toBeInstanceOf(SynchronizationData::class)
        ->bank->toBe($bank)
        ->token->toBe($token);
});
