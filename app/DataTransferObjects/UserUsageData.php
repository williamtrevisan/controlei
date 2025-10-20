<?php

namespace App\DataTransferObjects;

use App\Actions\GetUserImportUsage;
use App\Actions\GetUserSynchronizationUsage;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;

readonly class UserUsageData implements Arrayable
{
    public function __construct(
        public Usage $accounts,
        public Usage $synchronizations,
        public Usage $imports,
        public ?int $historyDays,
    ) {}

    public static function from(
        User $user,
        GetUserSynchronizationUsage $getUserSynchronizationUsage,
        GetUserImportUsage $getUserImportUsage
    ): self {
        $plan = $user->plan;
        $accountsCount = $user->accounts()->count();

        return new self(
            accounts: new Usage(
                current: $accountsCount,
                limit: $plan->max_accounts,
                percentage: ($accountsCount / $plan->max_accounts) * 100,
            ),
            synchronizations: $getUserSynchronizationUsage->execute($user),
            imports: $getUserImportUsage->execute($user),
            historyDays: $plan->history_days,
        );
    }

    public function toArray(): array
    {
        return [
            'accounts' => $this->accounts->toArray(),
            'synchronizations' => $this->synchronizations->toArray(),
            'imports' => $this->imports->toArray(),
            'history_days' => $this->historyDays,
        ];
    }
}

