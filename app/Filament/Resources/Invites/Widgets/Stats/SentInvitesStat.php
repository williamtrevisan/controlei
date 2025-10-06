<?php

namespace App\Filament\Resources\Invites\Widgets\Stats;

use App\Actions\GetAllUserSentInvitesByPeriod;
use App\Filament\Resources\Invites\Widgets\Concerns\AggregatesInvites;
use App\Models\Invite;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class SentInvitesStat
{
    use AggregatesInvites;

    public function __construct(
        private GetAllUserSentInvitesByPeriod $getAllSentInvitesByPeriod
    ) {}

    public function make(): Stat
    {
        /** @var Collection<int, Invite> $invites */
        $invites = $this->getAllSentInvitesByPeriod
            ->execute(now()->subMonth());

        $previousInvites = $this->getAllSentInvitesByPeriod
            ->execute(now()->subMonths(2))
            ->filter(fn ($invite) => $invite->created_at->lt(now()->subDays(30)));

        return Stat::make('Convites enviados', $invites->count())
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color(Color::Blue)
            ->description($this->description($invites, $previousInvites))
            ->chart($this->chart($invites));
    }

    /**
     * @param Collection<int, Invite> $current
     * @param Collection<int, Invite> $previous
     * @return string
     */
    private function description(Collection $current, Collection $previous): string
    {
        if ($previous->isEmpty()) {
            return 'Sem dados do período anterior';
        }

        $difference = $current->count() - $previous->count();
        $percentage = ($difference / $previous->count()) * 100;

        $sign = $difference >= 0 ? '+' : '';
        return sprintf('%+.1f%% (%s%d vs período anterior)', $percentage, $sign, $difference);
    }

    private function chart(Collection $invites): Collection
    {
        return $this->aggregateByDay($invites)
            ->sortKeys();
    }
}
