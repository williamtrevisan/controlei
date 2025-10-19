<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Actions\ClassifyExpenses;
use App\Models\Expense;
use Filament\Actions\BulkAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Descrição')
                    ->width('30%')
                    ->searchable(),

                TextColumn::make('category.description')
                    ->label('Categoria')
                    ->badge()
                    ->color(fn (Expense $record) => self::getColorFromName($record->category?->color))
                    ->icon(fn (Expense $record) => $record->category?->icon ? 'heroicon-o-' . $record->category->icon : null)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('frequency')
                    ->label('Periodicidade')
                    ->badge(),

                TextColumn::make('average_amount')
                    ->label('Média')
                    ->getStateUsing(function (Expense $expense) {
                        $amount = $expense->average_amount->formatTo('pt_BR');

                        return session()->get('hide_sensitive_data', false)
                            ? '****'
                            : $amount;
                    })
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('monthly_projection')
                    ->label('Projeção mensal')
                    ->getStateUsing(function (Expense $expense) {
                        $amount = $expense->getMonthlyProjection()?->formatTo('pt_BR') ?? null;
                        if (is_null($amount)) {
                            return null;
                        }

                        return session()->get('hide_sensitive_data', false)
                            ? '****'
                            : $amount;
                    })
                    ->alignEnd()
                    ->tooltip('Valor estimado mensal baseado na frequência'),

                IconColumn::make('active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->striped()
            ->toolbarActions([
                BulkAction::make('reclassify')
                    ->label('Reclassificar as transações')
                    ->icon(Heroicon::ArrowPath)
                    ->color('gray')
                    ->action(fn () => app()->make(ClassifyExpenses::class)->execute()),
            ])
            ->emptyStateHeading('Nenhuma despesa cadastrada.')
            ->emptyStateDescription('Crie uma conta primeiro e depois cadastre suas despesas para acompanhar seus gastos mensais.')
            ->emptyStateIcon(Heroicon::OutlinedArrowTrendingDown);
    }

    private static function getColorFromName(?string $colorName): array|string
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
}
