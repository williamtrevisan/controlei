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
                    ->getStateUsing(function (Expense $expense) {
                        $amount = $expense->average_amount->formatTo('pt_BR');
                        
                        return session()->get('hide_sensitive_data', false) 
                            ? str($amount)->replaceMatches('/\d/', '*')
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
                            ? str($amount)->replaceMatches('/\d/', '*')
                            : $amount;
                    })
                    ->alignEnd()
                    ->tooltip('Valor estimado mensal baseado na frequência'),

                IconColumn::make('active')
                    ->label('Status')
                    ->boolean(),
            ]);
    }
}
