<?php

namespace App\Actions;

use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionEventRepository;
use App\Repositories\Contracts\SubscriptionRepository;
use Illuminate\Support\Facades\DB;

readonly class RenewSubscription
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private SubscriptionEventRepository $subscriptionEventRepository
    ) {}

    public function execute(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            return tap(
                $this->subscriptionRepository->renew($subscription),
                fn (Subscription $subscription) => $this->subscriptionEventRepository->renew($subscription),
            );
        });
    }
}
