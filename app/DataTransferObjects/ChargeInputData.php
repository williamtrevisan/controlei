<?php

namespace App\DataTransferObjects;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

readonly class ChargeInputData
{
    public function __construct(
        public string $paymentId,
        public User $user,
        public Plan $plan,
        public int $amount,
        public string $action,
        public ?int $oldPlanId = null,
    ) {}

    public static function from(Payment $payment, int $amount, User $user, Subscription $subscription): self
    {
        $currentPlan = $user->plan;
        $newPlan = $subscription->plan;

        if ($currentPlan->id === $newPlan->id) {
            $action = 'renew';
            $oldPlanId = null;
        } elseif ($currentPlan->slug === 'free') {
            $action = 'create';
            $oldPlanId = null;
        } else {
            $action = 'upgrade';
            $oldPlanId = $currentPlan->id;
        }

        return new self(
            paymentId: $payment->id,
            user: $user,
            plan: $newPlan,
            amount: $amount,
            action: $action,
            oldPlanId: $oldPlanId,
        );
    }

    public function toArray(): array
    {
        return [
            'correlationID' => $this->paymentId,
            'value' => $this->amount,
            'customer' => [
                'correlationID' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'comment' => $this->comment(),
            'expiresDate' => now()->addDay()->toIso8601String(),
            'additionalInfo' => [
                ['key' => 'action', 'value' => $this->action],
                ['key' => 'old_plan_id', 'value' => (string) $this->oldPlanId],
            ],
        ];
    }

    private function comment(): string
    {
        $currentPlan = $this->user->plan;
        $newPlan = $this->plan;
        $email = $this->user->email;

        if ($currentPlan->id === $newPlan->id) {
            return match ($newPlan->slug) {
                'basic' => "[Controlei] Renovação confirmada → Basic (30 dias extras) | $email",
                'pro' => "[Controlei] Ciclo estendido → Pro (30 dias a mais) | $email",
                'premium' => "[Controlei] Power-up renovado → Premium (30 dias adicionais) | $email",
                default => "[Controlei] Renovação confirmada → $newPlan->name (30 dias extras) | $email",
            };
        }

        if ($currentPlan->slug === 'free') {
            return match ($newPlan->slug) {
                'basic' => "[Controlei] Acesso inicial ativado → Basic (30 dias) | $email",
                'pro' => "[Controlei] Power-up inicial ativado → Pro (30 dias) | $email",
                'premium' => "[Controlei] Boost de entrada aplicado → Premium (30 dias) | $email",
                default => "[Controlei] Acesso inicial ativado → $newPlan->name (30 dias) | $email",
            };
        }

        $currentSubscription = $this->user->subscriptions()
            ->where('plan_id', $currentPlan->id)
            ->where('status', 'active')
            ->first();

        $daysRemaining = 0;
        if ($currentSubscription) {
            $daysRemaining = now()->diffInDays($currentSubscription->ended_at);
        }

        $daysText = abs($daysRemaining).' '.(abs($daysRemaining) === 1 ? 'dia' : 'dias').' restantes';

        if ($newPlan->price > $currentPlan->price) {
            $transition = "$currentPlan->slug-$newPlan->slug";

            return match ($transition) {
                'free-basic' => "[Controlei] Upgrade ativado → Basic ($daysText) | $email",
                'free-pro', 'basic-pro' => "[Controlei] Boost aplicado → Pro ($daysText) | $email",
                'free-premium', 'basic-premium', 'pro-premium' => "[Controlei] Power-up ativado → Premium ($daysText) | $email",
                default => "[Controlei] Upgrade ativado → $newPlan->name ($daysText) | $email",
            };
        }

        return "[Controlei] Mudança aplicada → $newPlan->name ($daysText) | $email";
    }
}

