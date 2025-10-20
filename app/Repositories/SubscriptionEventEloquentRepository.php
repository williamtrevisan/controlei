<?php

namespace App\Repositories;

use App\Enums\SubscriptionEventType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionEvent;
use App\Models\User;
use App\Repositories\Contracts\SubscriptionEventRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionEventEloquentRepository implements SubscriptionEventRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {}

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    public function create(array $data): SubscriptionEvent
    {
        return $this->builder()
            ->create($data);
    }

    public function subscribe(User $user, Subscription $subscription, Plan $plan): SubscriptionEvent
    {
        return $this
            ->create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'event_type' => SubscriptionEventType::Subscribed,
                'from_id' => null,
                'to_id' => $plan->id,
                'monthly_recurring_revenue_change' => $plan->price,
            ]);
    }

    public function cancel(Subscription $subscription): SubscriptionEvent
    {
        return $this
            ->create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'event_type' => SubscriptionEventType::Canceled,
                'from_id' => $subscription->plan_id,
                'to_id' => null,
                'monthly_recurring_revenue_change' => -$subscription->plan->price,
            ]);
    }

    public function expire(Subscription $subscription, Plan $freePlan): SubscriptionEvent
    {
        return $this
            ->create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'event_type' => SubscriptionEventType::Expired,
                'from_id' => $subscription->plan_id,
                'to_id' => $freePlan->id,
                'monthly_recurring_revenue_change' => -$subscription->plan->price,
            ]);
    }

    public function renew(Subscription $subscription): SubscriptionEvent
    {
        return $this
            ->create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'event_type' => SubscriptionEventType::Renewed,
                'from_id' => $subscription->plan_id,
                'to_id' => $subscription->plan_id,
                'monthly_recurring_revenue_change' => 0,
            ]);
    }

    public function upgrade(Subscription $subscription, Plan $oldPlan, Plan $newPlan): SubscriptionEvent
    {
        return $this
            ->create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'event_type' => SubscriptionEventType::Upgraded,
                'from_id' => $oldPlan->id,
                'to_id' => $newPlan->id,
                'monthly_recurring_revenue_change' => $newPlan->price - $oldPlan->price,
            ]);
    }
}

