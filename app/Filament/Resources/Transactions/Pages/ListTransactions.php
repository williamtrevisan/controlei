<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Actions\ClassifyTransactions;
use App\Events\SynchronizationCompleted;
use App\Events\SynchronizationStarted;
use App\Filament\Imports\TransactionImporter;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Transactions\Widgets\MonthlyStatement;
use App\Jobs\FetchAndSynchronizeTransactions;
use App\Models\Synchronization;
use App\ValueObjects\StatementPeriod;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Bus;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;

    protected $listeners = ['privacy-toggled' => '$refresh'];

    protected static string $resource = TransactionResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->activeTab = (new StatementPeriod())->current()->value();
    }

    public function getBreadcrumb(): ?string
    {
        return 'Todas transações';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_sensitive_data')
                ->label('')
                ->tooltip(fn() => session()->get('hide_sensitive_data', false) ? 'Mostrar valores' : 'Ocultar valores')
                ->icon(fn() => session()->get('hide_sensitive_data', false) ? Heroicon::OutlinedEye : Heroicon::OutlinedEyeSlash)
                ->color('gray')
                ->action(function () {
                    $isHidden = ! session()->get('hide_sensitive_data', false);

                    session()->put('hide_sensitive_data', $isHidden);

                    $this->dispatch('privacy-toggled', hideData: $isHidden);
                }),

            Action::make('synchronize')
                ->label('Sincronizar transações')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->modalWidth('xl')
                ->modalHeading('Sincronizar transações')
                ->modalDescription('Use seu iToken para sincronizar as transações com o Itaú.')
                ->action(function (Action $action, array $data): void {
                    $synchronization = Synchronization::create([
                        'user_id' => auth()->id(),
                    ]);
                    $token = $data['token'];

                    SynchronizationStarted::dispatch($synchronization);

                    Bus::batch([
                        new FetchAndSynchronizeTransactions($synchronization, $token),
                    ])
                        ->name('syncing transactions')
                        ->onQueue('default')
//                        ->onConnection('database')
                        ->onConnection('sync')
                        ->allowFailures()
                        ->finally(function () use ($synchronization): void {
                            $synchronization->touch('completed_at');

                            SynchronizationCompleted::dispatch($synchronization);

                            Notification::make()
                                ->title('Sincronização concluída')
                                ->body('A sincronização das suas transações foi concluída.')
                                ->success()
                                ->sendToDatabase($synchronization->user, isEventDispatched: true);
                        })
                        ->dispatch();

                    $action->successNotification(
                        Notification::make()
                            ->title('Sincronização iniciada')
                            ->body('A sincronização foi iniciada e suas transações serão processadas em segundo plano.')
                            ->success(),
                    );
                })
                ->modalSubmitActionLabel('Sincronizar')
                ->modalFooterActionsAlignment(Alignment::End)
                ->schema([
                    TextInput::make('token')
                        ->label('iToken')
                        ->minLength(6)
                        ->maxLength(6)
                        ->required()
                        ->helperText('Código aleatório gerado pelo Itaú.')
                        ->placeholder('******'),
                ]),

            CreateAction::make('shared_expense')
                ->label('Gasto compartilhado')
                ->icon(Heroicon::OutlinedPlus)
                ->color('gray'),

            ActionGroup::make([
                ImportAction::make()
                    ->label('Importar transações')
                    ->importer(TransactionImporter::class)
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->color('gray')
                    ->modalFooterActionsAlignment(Alignment::End),

                Action::make('reclassify')
                    ->label('Reclassificar transações')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->action(fn () => app()->make(ClassifyTransactions::class)->execute()),
            ])
                ->icon(Heroicon::OutlinedEllipsisHorizontal)
                ->color('gray'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MonthlyStatement::class,
        ];
    }

    public function getTabs(): array
    {
        $current = (new StatementPeriod())->current();

        return [
            $current->previous()->value() => Tab::make((string) $current->previous())
                ->query(fn ($query) => $query->where('statement_period', $current->previous()->value()))
                ->icon(Heroicon::OutlinedLockClosed),

            $current->value() => Tab::make((string) $current)
                ->query(fn ($query) => $query->where('statement_period', $current->value()))
                ->icon(Heroicon::OutlinedLockOpen),

            $current->next()->value() => Tab::make((string) $current->next())
                ->query(fn ($query) => $query->where('statement_period', $current->next()->value()))
                ->icon(Heroicon::OutlinedClock),

            $current->advance(2)->value() => Tab::make((string) $current->advance(2))
                ->query(fn ($query) => $query->where('statement_period', $current->advance(2)->value()))
                ->icon(Heroicon::OutlinedClock),

            $current->advance(3)->value() => Tab::make((string) $current->advance(3))
                ->query(fn ($query) => $query->where('statement_period', $current->advance(3)->value()))
                ->icon(Heroicon::OutlinedClock),
        ];
    }


    public function getSubheading(): ?string
    {
        return 'Gerencie suas transações financeiras e acompanhe sua evolução mês a mês.';
    }

    public function getTitle(): string
    {
        return 'Minhas transações';
    }
}
