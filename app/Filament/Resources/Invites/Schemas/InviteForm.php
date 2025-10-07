<?php

namespace App\Filament\Resources\Invites\Schemas;

use App\Actions\CheckIfInviteExistsByInvitee;
use App\Actions\FindInviteeById;
use App\Actions\FindInviteeByInviteCode;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

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
                                Select::make('invitee_id')
                                    ->label('Usuário')
                                    ->required()
                                    ->searchable()
                                    ->placeholder('ABC-1234-DEF456')
                                    ->noSearchResultsMessage('Nenhum usuário encontrado com este código')
                                    ->searchPrompt('Busque o usuário pelo seu código de convite')
                                    ->helperText('Busque o usuário pelo seu código de convite')
                                    ->getSearchResultsUsing(function (string $search): array {
                                        if (strlen($search) !== 15) {
                                            return [];
                                        }

                                        return app()->make(FindInviteeByInviteCode::class)
                                            ->execute($search)
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->getOptionLabelUsing(function (string $value): Collection {
                                        return app()->make(FindInviteeById::class)
                                            ->execute($value)
                                            ->pluck('name', 'id');
                                    })
                                    ->rules([
                                        fn (): Closure => function (string $attribute, string $id, Closure $fail) {
                                            if ($id === auth()->id()) {
                                                $fail('Você não pode enviar um convite para si mesmo.');

                                                return;
                                            }

                                            if (! $invitee = app()->make(FindInviteeById::class)->execute($id)) {
                                                $fail('Código de convite inválido. Verifique e tente novamente.');

                                                return;
                                            }

                                            if (! app()->make(CheckIfInviteExistsByInvitee::class)->execute($invitee)) {
                                                return;
                                            }

                                            $fail('Você já enviou um convite para este usuário.');
                                        },
                                    ]),

                                Textarea::make('message')
                                    ->label('Mensagem')
                                    ->placeholder('Olá! Gostaria de me conectar com você para compartilhar dados financeiros.')
                                    ->maxLength(500)
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }
}

