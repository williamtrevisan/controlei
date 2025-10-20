<?php

namespace App\Actions;

use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionEventRepository;
use App\Repositories\Contracts\SubscriptionRepository;
use Illuminate\Support\Facades\DB;

class CancelSubscription
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly SubscriptionEventRepository $subscriptionEventRepository
    ) {}

    public function execute(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            return tap(
                $this->subscriptionRepository->cancel($subscription),
                fn (Subscription $subscription) => $this->subscriptionEventRepository->cancel($subscription),
            );
        });
    }
}
