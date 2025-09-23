<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountBank;
use App\Enums\AccountType;
use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Tiptap\Nodes\Text;

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
                                    ->options(AccountType::class)
                                    ->default(AccountType::Checking)
                                    ->placeholder('Selecione o tipo de conta')
                                    ->searchable(),

                                Select::make('bank')
                                    ->label('Instituição financeira')
                                    ->required()
                                    ->options(AccountBank::class)
                                    ->placeholder('Selecione a instituição financeira')
                                    ->searchable(),

                                TextInput::make('agency')
                                    ->label('Agência')
                                    ->required()
                                    ->length(4)
                                    ->mask('9999')
                                    ->placeholder('9999')
                                    ->helperText('Número da agência bancária'),

                                TextInput::make('account')
                                    ->hidden()
                                    ->dehydratedWhenHidden(),

                                TextInput::make('account_digit')
                                    ->hidden()
                                    ->dehydratedWhenHidden(),

                                TextInput::make('account_number')
                                    ->label('Conta')
                                    ->required()
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateHydrated(function (TextInput $component, ?Account $account, Set $set) {
                                        $component->state($account?->account_number);

                                        $set('account', $account?->account);
                                        $set('account_digit', $account?->account_digit);
                                    })
                                    ->afterStateUpdated(function (?string $state, Set $set) {
                                        [$account, $accountDigit] = str($state)
                                            ->explode('-');

                                        $set('account', $account);
                                        $set('account_digit', $accountDigit);
                                    })
                                    ->mask('99999-9')
                                    ->placeholder('99999-9')
                                    ->helperText('Número da sua conta com dígito verificador'),
                            ]),
                    ]),
            ]);
    }
}
