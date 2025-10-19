<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Actions\GetCategoryExpensesByStatementPeriod;
use App\Actions\GetTopExpenseCategoriesByStatementPeriod;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\ValueObjects\StatementPeriod;
use Brick\Money\Money;
use Filament\Support\Colors\Color;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class TopCategoriesStats extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Maiores gastos por categoria';

    protected $listeners = ['privacy-toggled' => '$refresh'];

    private Collection $previousCategories;

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    #[On('privacy-toggled')]
    public function refresh(): void
    {
    }

    public function getDescription(): ?string
    {
        return 'Top 3 categorias com maiores gastos no período atual, incluindo projeções de despesas fixas.';
    }

    protected function getStats(): array
    {
        $statementPeriod = $this->activeTab
            ? new StatementPeriod($this->activeTab)
            : (new StatementPeriod())->current();

        $categories = $this->getTopCategories($statementPeriod);

        if ($categories->isEmpty()) {
            return [
                Stat::make('Categorias', 'Sem dados')
                    ->description('Nenhuma despesa encontrada')
                    ->color(Color::Gray),
            ];
        }

        $this->previousCategories = $this->getPreviousCategories($statementPeriod);

        return $categories->map(fn ($category) => $this->makeCategoryStat($category))->toArray();
    }

    private function getTopCategories(StatementPeriod $statementPeriod): Collection
    {
        return app()->make(GetTopExpenseCategoriesByStatementPeriod::class)
            ->execute($statementPeriod, 3);
    }

    private function getPreviousCategories(StatementPeriod $statementPeriod): Collection
    {
        return app()->make(GetCategoryExpensesByStatementPeriod::class)
            ->execute($statementPeriod->previous())
            ->keyBy('category_id');
    }

    private function makeCategoryStat(array $category): Stat
    {
        $hideData = session()->get('hide_sensitive_data', false);
        $amount = $hideData ? '****' : $category['total']->formatTo('pt_BR');

        $previousCategory = $this->previousCategories->get($category['category_id']);
        $color = $this->getColorFromName($category['color']);

        $stat = Stat::make($category['category_name'], $amount)
            ->description($this->description($category['total'], $previousCategory))
            ->chart($this->chart($category['total'], $previousCategory))
            ->chartColor($color)
            ->color($color);

        if ($category['icon']) {
            $stat->icon('heroicon-o-' . $category['icon']);
        }

        return $stat;
    }

    private function description(Money $currentTotal, ?array $previousCategory): string
    {
        if (! $previousCategory) {
            return 'Sem dados do período anterior';
        }

        $previousTotal = $previousCategory['total'];

        if ($previousTotal->isZero()) {
            return 'Sem dados do período anterior';
        }

        $difference = $currentTotal->minus($previousTotal);
        $percentage = ($difference->getAmount()->toFloat() / $previousTotal->getAmount()->toFloat()) * 100;

        $sign = $difference->isPositiveOrZero() ? '+' : '';

        $formattedDifference = session()->get('hide_sensitive_data', false)
            ? '****'
            : $difference->formatTo('pt_BR');

        return sprintf('%+.1f%% (%s%s vs período anterior)', $percentage, $sign, $formattedDifference);
    }

    private function chart(Money $currentTotal, ?array $previousCategory): array
    {
        if (! $previousCategory) {
            return [7, 7, 7, 7, 7, 7, 7];
        }

        $previousTotal = $previousCategory['total'];
        $currentAmount = $currentTotal->getMinorAmount()->toInt();
        $previousAmount = $previousTotal->getMinorAmount()->toInt();

        if ($previousAmount === 0) {
            return [0, 2, 4, 6, 8, 10, 12];
        }

        if ($currentAmount > $previousAmount) {
            return [5, 8, 6, 10, 9, 12, 11];
        } elseif ($currentAmount < $previousAmount) {
            return [12, 11, 9, 10, 6, 8, 5];
        }

        return [7, 7, 7, 7, 7, 7, 7];
    }

    private function getColorFromName(?string $colorName): array|string
    {
        if (! $colorName) {
            return Color::Gray;
        }

        return match ($colorName) {
            'purple' => Color::Purple,
            'pink' => Color::Pink,
            'amber' => Color::Amber,
            'green' => Color::Green,
            'blue' => Color::Blue,
            'cyan' => Color::Cyan,
            'red' => Color::Red,
            'orange' => Color::Orange,
            'yellow' => Color::Yellow,
            'indigo' => Color::Indigo,
            'violet' => Color::Violet,
            'emerald' => Color::Emerald,
            'rose' => Color::Rose,
            'slate' => Color::Slate,
            'gray' => Color::Gray,
            default => Color::Gray,
        };
    }

    public function getColumns(): int|array|null
    {
        return [
            'default' => 1,
            'sm' => 3,
        ];
    }
}

