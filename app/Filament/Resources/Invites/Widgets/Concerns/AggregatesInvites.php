<?php

namespace App\Filament\Resources\Invites\Widgets\Concerns;

use App\Models\Invite;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait AggregatesInvites
{
    /**
     * @param Collection $invites
     * @return Collection<string, int>
     */
    public function aggregateByDay(Collection $invites): Collection
    {
        $lastWeekInvites = collect(CarbonPeriod::create(now()->subWeek(), now()))
            ->mapToGroups(fn (Carbon $date) => [
                $date->format('Y-m-d') => 0,
            ]);

        return $invites
            ->mapToGroups(fn (Invite $invite) => [
                $invite->created_at->format('Y-m-d') => 1,
            ])
            ->mergeRecursive($lastWeekInvites)
            ->map(function (array|Collection $aggregate): Collection {
                if (! is_array($aggregate)) {
                    return $aggregate;
                }

                return collect(array_values($aggregate))
                    ->flatten()
                    ->filter();
            })
            ->map(fn (Collection $counts) => $counts->sum());
    }
}
