<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountBank;
use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informações da conta')
                            ->schema([
                                Select::make('type')
                                    ->label('Tipo de conta')
                                    ->required()
                                    ->options([
                                        'checking' => 'Conta corrente',
                                        'savings' => 'Conta poupança',
                                        'wallet' => 'Conta digital',
                                        'investment' => 'Conta de investimentos',
                                    ])
                                    ->default('checking')
                                    ->placeholder('Selecione o tipo de conta')
                                    ->searchable(),

                                Select::make('bank')
                                    ->label('Instituição financeira')
                                    ->required()
                                    ->enum(AccountBank::class)
                                    ->placeholder('Selecione a instituição financeira')
                                    ->searchable(),

                                TextInput::make('agency')
                                    ->label('Agência')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('1234')
                                    ->helperText('Número da agência bancária'),

                                TextInput::make('account')
                                    ->label('Conta')
                                    ->required()
                                    ->mask('99999-9')
                                    ->formatStateUsing(fn (Account $account) => $account->account_number)
                                    ->placeholder('12345-6')
                                    ->helperText('Número da sua conta com dígito verificador'),
                            ]),
                    ]),
            ]);
    }
}
