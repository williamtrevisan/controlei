<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Actions\GetAllConnectedUsers;
use App\Actions\ShareManyTransactionsWithUser;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->getStateUsing(function (Transaction $transaction) {
                        $amount = $transaction->direction->isInflow()
                            ? $transaction->amount->formatTo('pt_BR')
                            : $transaction->amount->negated()->formatTo('pt_BR');

                        if (session()->get('hide_sensitive_data', false)) {
                            return '****';
                        }

                        return $amount;
                    })
                    ->money(currency: 'BRL', locale: 'pt_BR')
                    ->color(function (Transaction $transaction) {
                        if ($transaction->direction->isInflow()) {
                            return Color::Green;
                        }

                        return Color::Red;
                    })
                    ->alignment(Alignment::Right),

                TextColumn::make('installments')
                    ->label('Parcela')
                    ->alignment(Alignment::Center),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->visible(fn (?Transaction $transaction) => ! $transaction?->status->isPaid()),

                TextColumn::make('kind')
                    ->label('Tipo')
                    ->getStateUsing(function (Transaction $transaction) {
                        if ($incomeSource = $transaction->incomeSource) {
                            return $incomeSource->type;
                        }

                        return $transaction->kind;
                    })
                    ->badge(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(60)
                    ->tooltip(fn ($state) => str($state)->length() > 60 ? $state : null)
                    ->width('100%'),
            ])
            ->striped()
            ->checkIfRecordIsSelectableUsing(function (Transaction $record) {
                return $record->account->user_id === auth()->id();
            })
            ->groupedBulkActions([
                BulkAction::make('share')
                    ->label('Compartilhar transações')
                    ->icon(Heroicon::OutlinedShare)
                    ->modalWidth('xl')
                    ->modalHeading('Compartilhar transações selecionadas')
                    ->modalDescription('As transações selecionadas serão compartilhadas com o usuário escolhido. Todas as parcelas relacionadas também serão incluídas automaticamente.')
                    ->schema([
                        Select::make('user_id')
                            ->label('Usuário destinatário')
                            ->placeholder('Escolha com quem compartilhar')
                            ->options(function () {
                                return app()->make(GetAllConnectedUsers::class)
                                    ->execute()
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->helperText('Apenas usuários conectados através de convites aceitos aparecem nesta lista. Para conectar-se com novos usuários, acesse o menu "Convites".'),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $member = User::find($data['user_id']);

                        app()->make(ShareManyTransactionsWithUser::class)
                            ->execute($records, $member);

                        Notification::make()
                            ->success()
                            ->title('Transações compartilhadas!')
                            ->body("Transações foram compartilhadas com {$member->name}.")
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->visible(function () {
                        return app()->make(GetAllConnectedUsers::class)
                            ->execute()
                            ->isNotEmpty();
                    }),
            ])
            ->emptyStateHeading('Nenhuma transação encontrada.')
            ->emptyStateDescription('Sincronize com seu banco ou importe suas transações para começar. Contas e cartões serão criados automaticamente.')
            ->emptyStateIcon(Heroicon::OutlinedArrowTrendingUp);
    }
}