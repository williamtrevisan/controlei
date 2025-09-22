<?php

namespace App\Filament\Resources\IncomeSources\Tables;

use App\Actions\ClassifyTransactions;
use App\Models\IncomeSource;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IncomeSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Fonte')
                    ->width('40%')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Categoria')
                    ->badge(),

                TextColumn::make('frequency')
                    ->label('Periodicidade')
                    ->badge(),

                TextColumn::make('average_amount')
                    ->label('Média mensal')
                    ->getStateUsing(fn (IncomeSource $incomeSource) => $incomeSource->average_amount?->formatTo('pt_BR') ?? 'Não definido')
                    ->alignEnd()
                    ->sortable(),

                IconColumn::make('active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->toolbarActions([
                BulkAction::make('reclassify')
                    ->label('Reclassificar as transações')
                    ->icon(Heroicon::ArrowPath)
                    ->color('gray')
                    ->action(fn () => app()->make(ClassifyTransactions::class)->execute()),
            ]);
    }
}
