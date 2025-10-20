<?php

namespace App\Repositories;

use App\DataTransferObjects\ChargeInputData;
use App\DataTransferObjects\ChargeOutputData;
use App\Repositories\Contracts\ChargeRepository;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;

readonly class ChargeHttpRepository implements ChargeRepository
{
    public function __construct(
        private Factory|PendingRequest $http,
    ) {
    }

    public function charge(ChargeInputData $data): ChargeOutputData
    {
        return $this->http
            ->post('/charge', $data->toArray())
            ->collect('charge')
            ->pipe(fn ($charge) => ChargeOutputData::fromCollection($charge));
    }
}

