<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informações da categoria')
                            ->schema([
                                TextInput::make('description')
                                    ->label('Descrição')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ex: Alimentação'),

                                Toggle::make('active')
                                    ->label('Ativo')
                                    ->default(true)
                                    ->helperText('Categorias inativas não aparecerão nas opções de categorização'),
                            ]),
                    ]),
            ]);
    }
}
