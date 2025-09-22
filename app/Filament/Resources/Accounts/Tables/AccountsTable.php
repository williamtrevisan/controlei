<?php

namespace App\Filament\Resources\Accounts\Tables;

use App\Models\Account;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                ->label('Tipo')
                ->badge()
                ->sortable(),
                
                TextColumn::make('bank')
                    ->label('Instituição financeira')
                    ->width('20%')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agency')
                    ->label('Agência')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account_number')
                    ->label('Conta')
                    ->getStateUsing(fn (Account $account) => $account->account_number)
                    ->searchable(['account', 'account_digit']),
            ])
            ->striped()
            ->emptyStateHeading('Nenhuma conta disponível.')
            ->emptyStateDescription('Realize a criação de suas contas para começar.')
            ->emptyStateIcon(Heroicon::OutlinedCreditCard);
    }
}
