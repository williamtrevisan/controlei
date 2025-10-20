<?php

namespace App\Repositories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Repositories\Contracts\SubscriptionRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SubscriptionEloquentRepository implements SubscriptionRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {}

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    public function create(array $data): Subscription
    {
        return $this->builder()
            ->create($data);
    }

    public function subscribe(User $user, Plan $plan): Subscription
    {
        return $this
            ->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Active,
                'started_at' => now(),
                'ended_at' => now()->addMonth(),
            ]);
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        return tap($subscription)
            ->update($data);
    }

    public function cancel(Subscription $subscription): Subscription
    {
        return tap($subscription)
            ->touch('canceled_at');
    }

    public function expire(Subscription $subscription): Subscription
    {
        return $this
            ->update($subscription, [
                'status' => SubscriptionStatus::Expired,
            ]);
    }

    public function renew(Subscription $subscription): Subscription
    {
        return $this
            ->update($subscription, [
                'started_at' => $subscription->ended_at,
                'ended_at' => $subscription->ended_at->copy()->addMonth(),
            ]);
    }

    public function upgrade(Subscription $subscription, Plan $newPlan): Subscription
    {
        return $this
            ->update($subscription, [
                'plan_id' => $newPlan->id,
                'started_at' => now(),
                'ended_at' => now()->addMonth(),
                'canceled_at' => null,
            ]);
    }

    public function findActiveByUser(User $user): ?Subscription
    {
        return $this->builder()
            ->where('user_id', $user->id)
            ->where('status', SubscriptionStatus::Active)
            ->latest()
            ->first();
    }

    /**
     * @param User $user
     * @return Collection<int, Subscription>
     */
    public function findByUser(User $user): Collection
    {
        return $this->builder()
            ->where('user_id', $user->id)
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function findExpired(): Collection
    {
        return $this->builder()
            ->where('status', SubscriptionStatus::Active)
            ->where('ended_at', '<', now())
            ->get();
    }
}

