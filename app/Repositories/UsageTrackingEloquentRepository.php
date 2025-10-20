<?php

namespace App\Repositories;

use App\Enums\ResourceType;
use App\Models\User;
use App\Models\UsageTracking;
use App\Repositories\Contracts\UsageTrackingRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UsageTrackingEloquentRepository implements UsageTrackingRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {}

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    public function create(array $data): UsageTracking
    {
        return $this->builder()
            ->create($data);
    }

    public function find(User $user, ResourceType $resourceType): ?UsageTracking
    {
        return $this->builder()
            ->where('user_id', $user->id)
            ->where('resource_type', $resourceType)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();
    }

    public function initialize(User $user, ResourceType $resourceType): UsageTracking
    {
        return $this
            ->create([
                'user_id' => $user->id,
                'resource_type' => $resourceType,
                'year' => now()->year,
                'month' => now()->month,
                'count' => 1,
            ]);
    }

    public function increment(UsageTracking $usageTracking): void
    {
        $usageTracking->increment('count');
    }

    public function usage(User $user, ResourceType $resourceType): int
    {
        $usage = $this->builder()
            ->where('user_id', $user->id)
            ->where('resource_type', $resourceType)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        return $usage?->count ?? 0;
    }
}

