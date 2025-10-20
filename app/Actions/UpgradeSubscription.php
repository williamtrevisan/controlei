<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionEventRepository;
use App\Repositories\Contracts\SubscriptionRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Facades\DB;

readonly class UpgradeSubscription
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private SubscriptionEventRepository $subscriptionEventRepository,
        private UserRepository $userRepository
    ) {}

    public function execute(Subscription $subscription, Plan $oldPlan, Plan $newPlan): Subscription
    {
        return DB::transaction(function () use ($subscription, $oldPlan, $newPlan) {
            return tap(
                $this->subscriptionRepository->upgrade($subscription, $newPlan),
                function (Subscription $subscription) use ($oldPlan, $newPlan) {
                    $this->userRepository->update($subscription->user, ['plan_id' => $newPlan->id]);

                    $this->subscriptionEventRepository->upgrade($subscription, $oldPlan, $newPlan);
                },
            );
        });
    }
}
