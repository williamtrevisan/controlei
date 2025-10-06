<?php

namespace App\Filament\Resources\Invites\Widgets\Stats;

use App\Actions\GetAllUserAcceptedInvitesByPeriod;
use App\Actions\GetAllUserSentInvitesByPeriod;
use App\Filament\Resources\Invites\Widgets\Concerns\AggregatesInvites;
use App\Models\Invite;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class AcceptanceRateStat
{
    use AggregatesInvites;

    public function __construct(
        private GetAllUserSentInvitesByPeriod $getAllUserSentInvitesByPeriod,
        private GetAllUserAcceptedInvitesByPeriod $getAllUserAcceptedInvitesByPeriod
    ) {}

    public function make(): Stat
    {
        /** @var Collection<int, Invite> $sentInvites */
        $sentInvites = $this->getAllUserSentInvitesByPeriod
            ->execute(now()->subMonth());

        /** @var Collection<int, Invite> $acceptedInvites */
        $acceptedInvites = $this->getAllUserAcceptedInvitesByPeriod
            ->execute(now()->subMonth());

        $rate = $this->rate($sentInvites, $acceptedInvites);

        return Stat::make('Taxa de aceitação', number_format($rate) . '%')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color(Color::Green)
            ->description($this->description($sentInvites, $acceptedInvites))
            ->chart($this->chart($acceptedInvites));
    }

    /**
     * @param Collection<int, Invite> $sentInvites
     * @param Collection<int, Invite> $acceptedInvites
     * @return float
     */
    private function rate(Collection $sentInvites, Collection $acceptedInvites): float
    {
        if ($sentInvites->isEmpty()) {
            return 0.0;
        }

        return ($acceptedInvites->count() / $sentInvites->count()) * 100;
    }

    /**
     * @param Collection<int, Invite> $sentInvites
     * @param Collection<int, Invite> $acceptedInvites
     * @return string
     */
    private function description(Collection $sentInvites, Collection $acceptedInvites): string
    {
        if ($sentInvites->isEmpty() || $acceptedInvites->isEmpty()) {
            return 'Nenhum convite enviado';
        }

        $rate = $this->rate($sentInvites, $acceptedInvites);
        return sprintf('%.1f%% dos convites foram aceitos', $rate);
    }

    /**
     * @param Collection<int, Invite> $acceptedInvites
     * @return Collection<string, int>
     */
    private function chart(Collection $acceptedInvites): Collection
    {
        if ($acceptedInvites->isEmpty()) {
            return collect()
                ->times(7, fn (): int => 0);
        }

        return $this->aggregateByDay($acceptedInvites)
            ->sortKeys();
    }
}
