<?php

namespace App\Actions;

use App\DataTransferObjects\StatementData;
use App\Repositories\Contracts\StatementRepository;
use Illuminate\Support\Collection;

readonly class CreateManyStatements
{
    public function __construct(
        private StatementRepository $statementRepository,
    ) {
    }

    /**
     * @param Collection<int, StatementData> $statements
     * @return void
     */
    public function execute(Collection $statements): void
    {
        $this->statementRepository->createMany($statements);
    }
}


