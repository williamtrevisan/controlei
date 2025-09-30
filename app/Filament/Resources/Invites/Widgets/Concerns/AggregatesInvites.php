<?php

namespace App\Filament\Resources\Invites\Widgets\Concerns;

use App\Models\Invite;
use Illuminate\Support\Collection;

trait AggregatesInvites
{
    /**
     * @param Collection $invites
     * @return Collection<string, int>
     */
    public function aggregateByDay(Collection $invites): Collection
    {
        return $invites
            ->mapToGroups(fn (Invite $invite) => [
                $invite->created_at->format('Y-m-d') => 1,
            ])
            ->map(function (Collection $counts) {
                return $counts->sum();
            });
    }
}
