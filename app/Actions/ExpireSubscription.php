<?php

namespace App\Actions;

use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionEventRepository;
use App\Repositories\Contracts\SubscriptionRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Facades\DB;

readonly class ExpireSubscription
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private SubscriptionEventRepository $subscriptionEventRepository,
        private GetFreePlan $getFreePlan,
        private UserRepository $userRepository
    ) {}

    public function execute(Subscription $subscription): Subscription
    {
        $freePlan = $this->getFreePlan->execute();

        return DB::transaction(function () use ($subscription, $freePlan) {
            return tap(
                $this->subscriptionRepository->expire($subscription),
                function (Subscription $subscription) use ($freePlan) {
                    $this->userRepository->update($subscription->user, ['plan_id' => $freePlan->id]);
                    
                    $this->subscriptionEventRepository->expire($subscription, $freePlan);
                },
            );
        });
    }
}
