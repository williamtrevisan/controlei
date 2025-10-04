<?php

namespace App\Actions;

use App\Repositories\Contracts\StatementRepository;
use App\ValueObjects\StatementPeriod;
use Illuminate\Support\Collection;

class GetStatementPeriodsForTabs
{
    public function __construct(
        private readonly StatementRepository $statementRepository,
    ) {
    }

    /**
     * @return Collection<string, string>
     */
    public function execute(): Collection
    {
        $current = (new StatementPeriod())->current();
        
        return collect([
            $current->previous()->value() => (string) $current->previous(),
            $current->value() => (string) $current,
            $current->next()->value() => (string) $current->next(),
            $current->advance(2)->value() => (string) $current->advance(2),
            $current->advance(3)->value() => (string) $current->advance(3),
        ]);
    }
}
