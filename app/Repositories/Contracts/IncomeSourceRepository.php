<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface IncomeSourceRepository
{
    public function getActiveForMatching(): Collection;
}