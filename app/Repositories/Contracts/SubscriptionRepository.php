<?php

namespace App\Repositories\Contracts;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Collection;

interface SubscriptionRepository
{
    public function create(array $data): Subscription;

    public function subscribe(User $user, Plan $plan): Subscription;

    public function update(Subscription $subscription, array $data): Subscription;

    public function cancel(Subscription $subscription): Subscription;

    public function expire(Subscription $subscription): Subscription;

    public function renew(Subscription $subscription): Subscription;

    public function upgrade(Subscription $subscription, Plan $newPlan): Subscription;

    public function findActiveByUser(User $user): ?Subscription;

    /**
     * @param User $user
     * @return Collection<int, Subscription>
     */
    public function findByUser(User $user): Collection;

    /**
     * @return Collection<int, Subscription>
     */
    public function findExpired(): Collection;
}

