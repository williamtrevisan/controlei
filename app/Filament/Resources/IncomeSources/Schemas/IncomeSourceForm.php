<?php

namespace App\Filament\Resources\IncomeSources\Schemas;

use App\Enums\IncomeFrequency;
use App\Enums\IncomeSourceType;
use App\Models\IncomeSource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class IncomeSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informações da fonte de renda')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Fonte')
                                    ->placeholder('Ex.: Salário Empresa X')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug', str($state)->kebab());
                                    }),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->unique()
                                    ->required()
                                    ->placeholder('salario-empresa-x')
                                    ->helperText('Usado em URLs e integrações. Pode ajustar se quiser.'),

                                Select::make('type')
                                    ->label('Categoria')
                                    ->options(IncomeSourceType::class)
                                    ->required()
                                    ->searchable(),

                                Select::make('frequency')
                                    ->label('Periodicidade')
                                    ->options(IncomeFrequency::class)
                                    ->default(IncomeFrequency::Monthly)
                                    ->required()
                                    ->live()
                                    ->helperText(function ($state) {
                                        if (!$state) return 'Selecione a frequência desta fonte de renda';
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
                                            'annually' => 'Média anual',
                                            'occasionally' => 'Média por ocorrência',
                                            default => 'Média mensal',
                                        };
                                    })
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
                                    ->formatStateUsing(function (?IncomeSource $incomeSource): string {
                                        return (string) $incomeSource?->average_amount->getAmount();
                                    })
                                    ->helperText(function ($get) {
                                        $frequency = $get('frequency');
                                        return match ($frequency) {
                                            'annually' => 'Valor anual (ex: 13º salário). NÃO será usado para projeções mensais.',
                                            'occasionally' => 'Valor médio quando ocorre (ex: FGTS). NÃO será usado para projeções mensais.',
                                            default => 'Média mensal desta fonte de renda.',
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
