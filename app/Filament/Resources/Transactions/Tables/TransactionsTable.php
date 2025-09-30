<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->getStateUsing(function (Transaction $transaction) {
                        $amount = $transaction->direction->isInflow()
                            ? $transaction->amount->formatTo('pt_BR')
                            : $transaction->amount->negated()->formatTo('pt_BR');
                        
                        if (session()->get('hide_sensitive_data', false)) {
                            return '****';
                        }
                        
                        return $amount;
                    })
                    ->money(currency: 'BRL', locale: 'pt_BR')
                    ->color(function (Transaction $transaction) {
                        if ($transaction->direction->isInflow()) {
                            return Color::Green;
                        }

                        return Color::Red;
                    })
                    ->alignment(Alignment::Right),

                TextColumn::make('installments')
                    ->label('Parcela')
                    ->alignment(Alignment::Center),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->visible(fn (?Transaction $transaction) => ! $transaction?->status->isPaid()),

                TextColumn::make('kind')
                    ->label('Tipo')
                    ->getStateUsing(function (Transaction $transaction) {
                        if ($incomeSource = $transaction->incomeSource) {
                            return $incomeSource->type;
                        }

                        return $transaction->kind;
                    })
                    ->badge(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(60)
                    ->tooltip(fn ($state) => str($state)->length() > 60 ? $state : null)
                    ->width('100%'),
            ])
            ->striped()
            ->emptyStateHeading('Nenhuma transação encontrada.')
            ->emptyStateDescription('Sincronize com seu banco ou importe suas transações para começar. Contas e cartões serão criados automaticamente.')
            ->emptyStateIcon(Heroicon::OutlinedArrowTrendingUp);
    }
}
