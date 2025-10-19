<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\Expense;
use App\ValueObjects\StatementPeriod;
use Brick\Money\Money;
use Illuminate\Support\Collection;

class GetCategoryExpensesByStatementPeriod
{
    public function __construct(
        private GetAllExpenseTransactionsByStatementPeriod $getAllExpenseTransactionsByStatementPeriod,
        private GetAllUserExpenses $getAllUserExpenses
    ) {}

    /**
     * Get expenses aggregated by category for a statement period
     *
     * @param StatementPeriod $statementPeriod
     * @return Collection<int, array{category_id: ?int, category_name: string, icon: ?string, color: ?string, total: Money, count: int}>
     */
    public function execute(StatementPeriod $statementPeriod): Collection
    {
        $transactions = $this->getAllExpenseTransactionsByStatementPeriod
            ->execute($statementPeriod)
            ->load('category');

        // Group transactions by category
        $categoryData = $transactions
            ->groupBy('category_id')
            ->map(function (Collection $categoryTransactions, ?int $categoryId) {
                $category = $categoryTransactions->first()?->category;
                
                return [
                    'category_id' => $categoryId,
                    'category_name' => $category?->description ?? 'Sem categoria',
                    'icon' => $category?->icon,
                    'color' => $category?->color,
                    'total' => $categoryTransactions->reduce(
                        fn (Money $carry, $transaction) => $carry->plus($transaction->amount),
                        money()->of(0)
                    ),
                    'count' => $categoryTransactions->count(),
                ];
            });

        // Add projected expenses for future/current periods
        if (! $statementPeriod->isPast()) {
            $projectedByCategory = $this->calculateProjectedExpensesByCategory($statementPeriod);
            
            // Convert to array for modification
            $categoryArray = $categoryData->toArray();
            
            foreach ($projectedByCategory as $categoryId => $projectedAmount) {
                if (isset($categoryArray[$categoryId])) {
                    // Add projection to existing category
                    $categoryArray[$categoryId]['total'] = $categoryArray[$categoryId]['total']->plus($projectedAmount);
                } else {
                    // Create new category entry for projection
                    $category = Category::find($categoryId);
                    
                    $categoryArray[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $category?->description ?? 'Sem categoria',
                        'icon' => $category?->icon,
                        'color' => $category?->color,
                        'total' => $projectedAmount,
                        'count' => 0, // No actual transactions yet
                    ];
                }
            }
            
            // Convert back to collection
            $categoryData = collect($categoryArray);
        }

        return $categoryData
            ->sortByDesc(fn (array $category) => $category['total']->getMinorAmount()->toInt())
            ->values();
    }

    private function calculateProjectedExpensesByCategory(StatementPeriod $statementPeriod): Collection
    {
        // Get expense IDs that already have transactions in this period
        $expenseIdsWithTransactions = $this->getAllExpenseTransactionsByStatementPeriod
            ->execute($statementPeriod)
            ->whereNotNull('expense_id')
            ->pluck('expense_id')
            ->unique();

        // Get all expenses that haven't been realized yet
        return $this->getAllUserExpenses
            ->execute()
            ->reject(fn (Expense $expense) => $expenseIdsWithTransactions->contains($expense->id))
            ->filter(fn (Expense $expense) => $expense->category_id !== null) // Only expenses with categories
            ->groupBy('category_id')
            ->map(function (Collection $expenses) {
                return $expenses->reduce(function (Money $carry, Expense $expense) {
                    $monthlyProjection = $expense->getMonthlyProjection();
                    
                    return $monthlyProjection
                        ? $carry->plus($monthlyProjection)
                        : $carry;
                }, money()->of(0));
            })
            ->filter(fn (Money $amount) => ! $amount->isZero()); // Remove categories with zero projection
    }
}

