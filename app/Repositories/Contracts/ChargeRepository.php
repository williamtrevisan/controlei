<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\ChargeInputData;
use App\DataTransferObjects\ChargeOutputData;

interface ChargeRepository
{
    public function charge(ChargeInputData $data): ChargeOutputData;
}
