<?php

namespace App\Actions;

use App\Repositories\Contracts\StatementRepository;
use Illuminate\Support\Collection;

readonly class GetAllUserStatements
{
    public function __construct(
        private StatementRepository $statementRepository
    ) {
    }

    public function execute(): Collection
    {
        return $this->statementRepository->getAllUserStatements();
    }
}

