<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Models\Expense;
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
                    ->width('40%')
                    ->searchable(),

                TextColumn::make('frequency')
                    ->label('Periodicidade')
                    ->badge(),

                TextColumn::make('average_amount')
                    ->label('Média')
                    ->getStateUsing(fn (Expense $expense) => $expense->average_amount->formatTo('pt_BR'))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('monthly_projection')
                    ->label('Projeção mensal')
                    ->getStateUsing(fn (Expense $expense) => $expense->getMonthlyProjection()?->formatTo('pt_BR') ?? '—')
                    ->alignEnd()
                    ->tooltip('Valor estimado mensal baseado na frequência'),

                IconColumn::make('active')
                    ->label('Status')
                    ->boolean(),
            ]);
    }
}
