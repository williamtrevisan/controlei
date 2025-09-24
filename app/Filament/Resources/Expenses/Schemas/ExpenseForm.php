<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Enums\ExpenseFrequency;
use App\Models\Expense;
use chillerlan\QRCode\Common\MaskPattern;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informações da despesa')
                            ->schema([
                                TextInput::make('description')
                                    ->label('Descrição')
                                    ->placeholder('Ex.: Aluguel')
                                    ->required(),

                                Select::make('frequency')
                                    ->label('Periodicidade')
                                    ->options(ExpenseFrequency::class)
                                    ->default(ExpenseFrequency::Monthly)
                                    ->required()
                                    ->live()
                                    ->helperText(function ($state) {
                                        if (!$state) return 'Selecione a frequência desta despesa';
                                        return $state->getDescription();
                                    }),

                                TextInput::make('matcher_regex')
                                    ->label('Regex para identificação')
                                    ->placeholder('/^(some*.thing)/i')
                                    ->helperText('Use delimitadores (ex.: /…/i). O regex é aplicado sobre a descrição do lançamento.')
                                    ->required(),

                                TextInput::make('average_amount')
                                    ->label(function ($get) {
                                        $frequency = $get('frequency');

                                        return match ($frequency) {
                                            'quarterly' => 'Média trimestral',
                                            'annually' => 'Média anual',
                                            'occasionally' => 'Média por ocorrência',
                                            default => 'Média mensal',
                                        };
                                    })
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
                                    ->default(0)
                                    ->afterStateHydrated(function (TextInput $component, ?Expense $expense) {
                                        $component->state((string) $expense?->average_amount->getAmount());
                                    })
                                    ->required()
                                    ->prefix('R$')
                                    ->placeholder('R$ 0,00')
                                    ->helperText(function ($get) {
                                        $frequency = $get('frequency');
                                        return match ($frequency) {
                                            'quarterly' => 'Valor trimestral (ex: IPTU). Será dividido por 3 para projeções mensais.',
                                            'annually' => 'Valor anual (ex: seguro). Será dividido por 12 para projeções mensais.',
                                            'occasionally' => 'Valor médio quando ocorre (ex: viagens). NÃO será usado para projeções mensais.',
                                            default => 'Média mensal desta categoria de despesa.',
                                        };
                                    }),

                                Toggle::make('active')
                                    ->label('Ativo')
                                    ->required()
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
