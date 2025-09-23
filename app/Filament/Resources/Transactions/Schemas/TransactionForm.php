<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Actions\GetAllSharedCards;
use App\Models\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informações da transação')
                            ->schema([
                                Select::make('card_id')
                                    ->label('Cartão')
                                    ->options(function () {
                                        return app()->make(GetAllSharedCards::class)
                                            ->execute()
                                            ->mapWithKeys(fn (Card $card): array => [
                                                $card->id => $card->last_four_digits
                                            ]);
                                    })
                                    ->required()
                                    ->placeholder('Selecione o cartão usado na compra')
                                    ->searchable()
                                    ->helperText('Apenas cartões compartilhados aparecem aqui'),

                                DatePicker::make('date')
                                    ->label('Data da compra')
                                    ->default(now())
                                    ->required()
                                    ->helperText('Data em que a compra foi realizada no cartão'),

                                TextInput::make('description')
                                    ->label('Descrição')
                                    ->required()
                                    ->placeholder('Geladeira Magazine Luiza')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('amount')
                                    ->label('Valor')
                                    ->default(0)
                                    ->required()
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
                                    ->helperText('Valor da parcela'),

                                Select::make('total_installments')
                                    ->label('Parcelas')
                                    ->options(function () {
                                        return collect()
                                            ->range(1, 24)
                                            ->mapWithKeys(fn ($installment) => [
                                                $installment => $installment === 1 ? '1x (à vista)' : "{$installment}x"
                                            ]);
                                    })
                                    ->default(1)
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->helperText('Total de parcelas da compra'),

                                Toggle::make('is_existing_purchase')
                                    ->label('Já paguei algumas parcelas')
                                    ->default(false)
                                    ->dehydrated(false)
                                    ->live()
                                    ->helperText('Ative se esta compra já foi parcelada e você quer continuar de onde parou'),

                                Select::make('current_installment')
                                    ->label('Parcela atual')
                                    ->options(function (callable $get) {
                                        $totalInstallments = $get('total_installments') ?? 1;
                                        return collect()
                                            ->range(1, $totalInstallments)
                                            ->mapWithKeys(fn ($installment) => [
                                                $installment => "$installment de $totalInstallments"
                                            ]);
                                    })
                                    ->default(1)
                                    ->required()
                                    ->searchable()
                                    ->visible(fn (callable $get) => $get('is_existing_purchase'))
                                    ->helperText('As parcelas anteriores serão marcadas como já pagas.'),
                            ]),
                    ]),
            ]);
    }
}
