<?php

namespace App\Filament\Resources\Invites\Widgets;

use App\Filament\Resources\Invites\Widgets\Stats\AcceptanceRateStat;
use App\Filament\Resources\Invites\Widgets\Stats\PendingInvitesStat;
use App\Filament\Resources\Invites\Widgets\Stats\SentInvitesStat;
use Filament\Widgets\StatsOverviewWidget;

class InviteOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            app()->make(SentInvitesStat::class)->make(),
            app()->make(PendingInvitesStat::class)->make(),
            app()->make(AcceptanceRateStat::class)->make(),
        ];
    }

    public function getColumns(): int|array|null
    {
        return ['default' => 1, 'md' => 3];
    }
}
