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
            ->emptyStateHeading('Nenhuma conta cadastrada.')
            ->emptyStateDescription('Crie sua primeira conta bancária para começar a gerenciar suas transações financeiras.')
            ->emptyStateIcon(Heroicon::OutlinedBuildingLibrary);
    }
}
