<?php

namespace App\Filament\Resources\Invites\Pages;

use App\Actions\GetAllUserReceivedInvites;
use App\Actions\GetAllUserSentInvites;
use App\Filament\Resources\Invites\InviteResource;
use App\Filament\Resources\Invites\Widgets\InviteOverviewWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListInvites extends ListRecords
{
    protected static string $resource = InviteResource::class;

    public function getBreadcrumb(): ?string
    {
        return 'Convites';
    }

    public function getTitle(): string
    {
        return 'Convites';
    }

    public function getSubheading(): ?string
    {
        return 'Gerencie convites enviados e recebidos.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invite_code')
                ->label(auth()->user()->invite_code)
                ->icon(Heroicon::OutlinedClipboardDocument)
                ->color('gray')
                ->extraAttributes([
                    'data-copyable' => auth()->user()->invite_code,
                ], true)
                ->alpineClickHandler(<<<'JS'
                    navigator.clipboard
                        .writeText(event.currentTarget.dataset.copyable)
                        .then(() => {
                            $tooltip('Copiado!', { timeout: 3000 });
                        });
                JS),

            CreateAction::make()
                ->label('Novo registro')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }

    public function getTabs(): array
    {
        $received = app()->make(GetAllUserReceivedInvites::class)->execute();
        $sent = app()->make(GetAllUserSentInvites::class)->execute();

        return [
            'received' => Tab::make('Recebidos')
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->badge(fn () => $received->count())
                ->badgeColor(fn (Tab $tab) => $this->activeTab === 'received' ? 'primary' : 'gray')
                ->query(fn (Builder $query) => $query->where('invitee_id', auth()->id())),

            'sent' => Tab::make('Enviados')
                ->formatStateUsing(fn () => [])
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->badge(fn () => $sent->count())
                ->badgeColor(fn (Tab $tab) => $this->activeTab === 'sent' ? 'primary' : 'gray')
                ->query(fn (Builder $query) => $query->where('inviter_id', auth()->id())),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'received';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InviteOverviewWidget::class,
        ];
    }
}
