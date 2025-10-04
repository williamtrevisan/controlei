<?php

namespace App\Actions;

use App\Repositories\Contracts\IncomeSourceRepository;
use Illuminate\Support\Collection;

class GetAllUserIncomeSource
{
    public function __construct(
        private readonly IncomeSourceRepository $incomeSourceRepository
    ) {
    }

    public function execute(): Collection
    {
        return $this->incomeSourceRepository->getActiveForMatching();
    }
}
