<?php

namespace App\Repositories\Contracts;

use App\Models\IncomeSource;
use Illuminate\Support\Collection;

interface IncomeSourceRepository
{
    public function actives(): Collection;

    /**
     * @return Collection<int, IncomeSource>
     */
    public function monthly(): Collection;
}
