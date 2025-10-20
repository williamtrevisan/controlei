<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Repositories\Contracts\SubscriptionEventRepository;
use App\Repositories\Contracts\SubscriptionRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Facades\DB;

class CreateSubscription
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly SubscriptionEventRepository $subscriptionEventRepository,
        private readonly UserRepository $userRepository
    ) {}

    public function execute(User $user, Plan $plan): Subscription
    {
        return DB::transaction(function () use ($user, $plan) {
            return tap(
                $this->subscriptionRepository->subscribe($user, $plan),
                function (Subscription $subscription) use ($user, $plan) {
                    $this->userRepository->update($user, ['plan_id' => $plan->id]);
                    $this->subscriptionEventRepository->subscribe($user, $subscription, $plan);
                },
            );
        });
    }
}
