<?php

namespace App\Filament\Resources\Cards\Schemas;

use App\Enums\CardBrand;
use App\Enums\CardType;
use App\Models\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class CardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informações do cartão')
                            ->schema([
                                Select::make('account_id')
                                    ->label('Conta')
                                    ->relationship('account', 'account_number')
                                    ->required()
                                    ->placeholder('Selecione a conta a qual o cartão é pertencente'),

                                TextInput::make('last_four_digits')
                                    ->label('Últimos quatro digitos')
                                    ->required()
                                    ->length(4)
                                    ->placeholder('Ex: 1111'),

                                TextInput::make('matcher_regex')
                                    ->label('Regex para identificação do pagamento na fatura')
                                    ->required()
                                    ->placeholder('Ex: '),

                                Select::make('type')
                                    ->options(CardType::class)
                                    ->label('Tipo de conta')
                                    ->required()
                                    ->default(CardType::Credit)
                                    ->placeholder('Selecione o tipo de conta')
                                    ->searchable(),

                                Select::make('brand')
                                    ->options(CardBrand::class)
                                    ->label('Bandeira')
                                    ->required()
                                    ->default(CardBrand::Mastercard)
                                    ->placeholder('Selecione a bandeira do cartão')
                                    ->searchable(),

                                TextInput::make('limit')
                                    ->label('Limite')
                                    ->prefix('R$')
                                    ->mask(RawJs::make(<<<'JS'
                                        function formatter(value) {
                                            let numbers = value.replace(/\D/g, '');
                                            if (! numbers) {
                                                return '';
                                            }

                                            let amount = parseInt(numbers) / 100;

                                            return amount.toLocaleString('pt-BR', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        }
                                    JS))
                                    ->placeholder('R$ 0,00')
                                    ->formatStateUsing(function (Card $card): string {
                                        return (string) $card->limit->getAmount();
                                    }),

                                TextInput::make('due_day')
                                    ->label('Dia de vencimento')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(31)
                                    ->live(onBlur: true),

                                TextInput::make('closing_day')
                                    ->label('Dia de fechamento (mês atual)')
                                    ->formatStateUsing(fn (Card $card) => $card->closing_day)
                                    ->disabled()
                                    ->helperText('Calculado automaticamente baseado no banco e dia de vencimento'),
                            ]),
                    ]),
            ]);
    }
}
