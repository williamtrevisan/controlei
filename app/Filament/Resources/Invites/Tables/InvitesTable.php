<?php

namespace App\Filament\Resources\Invites\Tables;

use App\Enums\InvitationStatus;
use App\Models\Invite;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class InvitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inviter.name')
                    ->label('Remetente')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('message')
                    ->label('Mensagem')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->message)
                    ->placeholder('Sem mensagem')
                    ->color(Color::Gray),

                TextColumn::make('status')
                    ->label('Situação')
                    ->badge()
                    ->color(fn (InvitationStatus $state): string => match ($state) {
                        InvitationStatus::Pending => 'warning',
                        InvitationStatus::Accepted => 'success',
                        InvitationStatus::Rejected => 'danger',
                        InvitationStatus::Blocked => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Recebido')
                    ->since()
                    ->sortable()
                    ->color(Color::Gray)
                    ->tooltip(fn ($record) => $record->created_at->format('d/m/Y H:i')),
            ])
            ->groupedBulkActions([
                BulkAction::make('accept_invites')
                    ->label('Aceitar convites')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aceitar convites selecionados')
                    ->modalDescription('Tem certeza que deseja aceitar todos os convites selecionados?')
                    ->modalSubmitActionLabel('Aceitar todos')
                    ->action(function (Collection $records) {
                        $acceptedCount = 0;

                        foreach ($records as $record) {
                            if ($record->status === InvitationStatus::Pending) {
                                $record->update([
                                    'status' => InvitationStatus::Accepted,
                                    'accepted_at' => now(),
                                ]);
                                $acceptedCount++;
                            }
                        }

                        if ($acceptedCount > 0) {
                            Notification::make()
                                ->title("$acceptedCount convite(s) aceito(s) com sucesso!")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Nenhum convite válido para aceitar')
                                ->warning()
                                ->send();
                        }
                    }),

                BulkAction::make('reject_invites')
                    ->label('Rejeitar convites')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rejeitar convites selecionados')
                    ->modalDescription('Tem certeza que deseja rejeitar todos os convites selecionados?')
                    ->modalSubmitActionLabel('Rejeitar todos')
                    ->action(function (Collection $records) {
                        $rejectedCount = 0;

                        foreach ($records as $record) {
                            if ($record->status === InvitationStatus::Pending) {
                                $record->update([
                                    'status' => InvitationStatus::Rejected,
                                ]);
                                $rejectedCount++;
                            }
                        }

                        if ($rejectedCount > 0) {
                            Notification::make()
                                ->title("$rejectedCount convite(s) rejeitado(s)")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Nenhum convite válido para rejeitar')
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->checkIfRecordIsSelectableUsing(function (Invite $record) {
                return auth()->user()->is($record->invitee)
                    && $record->status->isPending();
            })
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('Nenhum convite recebido.')
            ->emptyStateDescription('Você não recebeu nenhum convite de outros usuários ainda.')
            ->emptyStateIcon(Heroicon::OutlinedInboxArrowDown);
    }
}
