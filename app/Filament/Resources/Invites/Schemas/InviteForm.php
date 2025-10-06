<?php

namespace App\Filament\Resources\Invites\Schemas;

use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class InviteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Enviar convite')
                            ->schema([
                                TextInput::make('invite_code')
                                    ->label('Código do usuário')
                                    ->required()
                                    ->maxLength(15)
                                    ->mask('***-****-******')
                                    ->extraInputAttributes(['oninput' => 'this.value = this.value.toUpperCase()'])
                                    ->placeholder('ABC-1234-DEF456')
                                    ->helperText('Digite o código do usuário que você deseja convidar'),

                                Textarea::make('message')
                                    ->label('Mensagem (opcional)')
                                    ->placeholder('Olá! Gostaria de me conectar com você para compartilhar dados financeiros.')
                                    ->maxLength(500)
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }
}
