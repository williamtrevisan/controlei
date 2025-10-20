<?php

namespace App\Repositories\Contracts;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionEvent;
use App\Models\User;

interface SubscriptionEventRepository
{
    public function create(array $data): SubscriptionEvent;

    public function subscribe(User $user, Subscription $subscription, Plan $plan): SubscriptionEvent;

    public function cancel(Subscription $subscription): SubscriptionEvent;

    public function expire(Subscription $subscription, Plan $freePlan): SubscriptionEvent;

    public function renew(Subscription $subscription): SubscriptionEvent;

    public function upgrade(Subscription $subscription, Plan $oldPlan, Plan $newPlan): SubscriptionEvent;
}

