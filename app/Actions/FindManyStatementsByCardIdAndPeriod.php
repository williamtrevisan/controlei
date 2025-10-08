<?php

namespace App\Actions;

use App\DataTransferObjects\StatementData;
use App\Repositories\Contracts\StatementRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

readonly class FindManyStatementsByCardIdAndPeriod
{
    public function __construct(
        private StatementRepository $statementRepository,
    ) {
    }

    /**
     * @param Collection<int, StatementData> $statements
     * @return Collection
     */
    public function execute(Collection $statements): Collection
    {
        if ($statements->isEmpty()) {
            return collect();
        }

        return $this->statementRepository->findManyBy(function (Builder $query) use ($statements) {
            $statements->each(function (StatementData $statement) use ($query) {
                $query->orWhere(function (Builder $query) use ($statement) {
                    $query->where('card_id', $statement->cardId)
                        ->where('period', $statement->period);
                });
            });
        });
    }
}

