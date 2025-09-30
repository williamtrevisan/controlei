<?php

namespace App\Filament\Resources\Invites\Widgets\Stats;

use App\Actions\GetAllPendingInvites;
use App\Filament\Resources\Invites\Widgets\Concerns\AggregatesInvites;
use App\Models\Invite;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class PendingInvitesStat
{
    use AggregatesInvites;

    public function __construct(
        private GetAllPendingInvites $getAllPendingInvites
    ) {}

    public function make(): Stat
    {
        /** @var Collection<int, Invite> $invites */
        $invites = $this->getAllPendingInvites
            ->execute(auth()->id());

        return Stat::make('Convites pendentes', $invites->count())
            ->icon(Heroicon::OutlinedClock)
            ->color(Color::Orange)
            ->description($this->description($invites))
            ->chart($this->chart($invites));
    }

    /**
     * @param Collection<int, Invite> $current
     * @return string
     */
    private function description(Collection $current): string
    {
        return trans_choice(
            '{0} Sem convites pendentes|{1} :count convite aguardando resposta|[2,*] :count convites aguardando resposta',
            $current->count(),
        );
    }

    /**
     * @param Collection<int, Invite> $invites
     * @return Collection<int, int>
     */
    private function chart(Collection $invites): Collection
    {
        if ($invites->isEmpty()) {
            return collect()
                ->times(7, fn (): int => 0);
        }

        return collect()
            ->times(7, fn (): int => $invites->count());
    }
}
