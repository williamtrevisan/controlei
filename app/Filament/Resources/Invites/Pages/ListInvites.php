<?php

namespace App\Filament\Resources\Invites\Pages;

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
        return [
            'received' => Tab::make('Recebidos')
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->badge(fn () => $this->getReceivedInvitesCount())
                ->badgeColor(fn (Tab $tab) => $this->activeTab === 'received' ? 'primary' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('invitee_id', auth()->id())),

            'sent' => Tab::make('Enviados')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->badge(fn () => $this->getSentInvitesCount())
                ->badgeColor(fn (Tab $tab) => $this->activeTab === 'sent' ? 'primary' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('inviter_id', auth()->id())),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'received';
    }

    protected function getReceivedInvitesCount(): int
    {
        return InviteResource::getEloquentQuery()
            ->where('invitee_id', auth()->id())
            ->count();
    }

    protected function getSentInvitesCount(): int
    {
        return InviteResource::getEloquentQuery()
            ->where('inviter_id', auth()->id())
            ->count();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InviteOverviewWidget::class,
        ];
    }
}
