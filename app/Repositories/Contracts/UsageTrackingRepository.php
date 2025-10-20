<?php

namespace App\Repositories\Contracts;

use App\Enums\ResourceType;
use App\Models\User;
use App\Models\UsageTracking;

interface UsageTrackingRepository
{
    public function create(array $data): UsageTracking;

    public function find(User $user, ResourceType $resourceType): ?UsageTracking;

    public function initialize(User $user, ResourceType $resourceType): UsageTracking;

    public function increment(UsageTracking $usageTracking): void;

    public function usage(User $user, ResourceType $resourceType): int;
}

