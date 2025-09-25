<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Actions\BatchCategorizeTransactions;
use App\Models\Category;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->defaultSort('description')
            ->striped()
            ->toolbarActions([
                BulkAction::make('categorize_transactions')
                    ->label('Categorizar transações')
                    ->icon(Heroicon::OutlinedCpuChip)
                    ->color('gray')
                    ->action(function (Collection $records, BatchCategorizeTransactions $batchCategorizeTransactions) {
                        $results = $batchCategorizeTransactions->execute(50);
                        
                        Notification::make()
                            ->title('Categorização concluída')
                            ->body("Processadas: {$results['processed']}, Categorizadas: {$results['categorized']}, Falhas: {$results['failed']}")
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Nenhuma categoria disponível.')
            ->emptyStateDescription('Crie suas categorias para organizar as transações.');
    }
}
