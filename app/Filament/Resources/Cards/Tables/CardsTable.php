<?php

namespace App\Filament\Resources\Cards\Tables;

use App\Actions\ClassifyTransactions;
use App\Models\Card;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.account_number')
                    ->label('Conta')
                    ->getStateUsing(fn (Card $card) => $card->account?->account_number)
                    ->sortable(),

                TextColumn::make('last_four_digits')
                    ->label('Cartão')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('brand')
                    ->label('Bandeira')
                    ->badge(),

                TextColumn::make('limit')
                    ->label('Limite')
                    ->getStateUsing(function (Card $card) {
                        if (!$card->limit) {
                            return null;
                        }
                        
                        $amount = $card->limit->formatTo('pt_BR');

                        return session()->get('hide_sensitive_data', false) 
                            ? '****'
                            : $amount;
                    })
                    ->money('BRL')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('closing_day')
                    ->label('Dia de fechamento')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('due_day')
                    ->label('Dia de vencimento')
                    ->numeric()
                    ->sortable(),
            ])
            ->striped()
            ->toolbarActions([
                BulkAction::make('reclassify')
                    ->label('Reclassificar as transações')
                    ->icon(Heroicon::ArrowPath)
                    ->color('gray')
                    ->action(fn () => app()->make(ClassifyTransactions::class)->execute()),
            ])
            ->emptyStateHeading('Nenhuma conta disponível.')
            ->emptyStateDescription('Realize a criação de suas contas para começar.')
            ->emptyStateIcon(Heroicon::OutlinedCreditCard);
    }
}
