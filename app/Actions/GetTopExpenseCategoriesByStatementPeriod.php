<?php

namespace App\Actions;

use App\ValueObjects\StatementPeriod;
use Brick\Money\Money;
use Illuminate\Support\Collection;

class GetTopExpenseCategoriesByStatementPeriod
{
    public function __construct(
        private GetCategoryExpensesByStatementPeriod $getCategoryExpensesByStatementPeriod
    ) {}

    /**
     * @param StatementPeriod $statementPeriod
     * @param int $limit
     * @return Collection<int, array{category_id: ?int, category_name: string, icon: ?string, color: ?string, total: Money, count: int, percentage: float}>
     */
    public function execute(StatementPeriod $statementPeriod, int $limit = 3): Collection
    {
        $categories = $this->getCategoryExpensesByStatementPeriod->execute($statementPeriod);
        if ($categories->isEmpty()) {
            return collect();
        }

        /** @var Money $expenses */
        $expenses = $categories
            ->reduce(fn ($carry, $category) => $carry->plus($category['total']), money()->of(0));

        return $categories
            ->take($limit)
            ->map(function (array $category) use ($expenses) {
                $percentage = $expenses->isZero()
                    ? 0.0
                    : ($category['total']->getAmount()->toFloat() / $expenses->getAmount()->toFloat()) * 100;

                return array_merge($category, ['percentage' => $percentage]);
            });
    }
}

