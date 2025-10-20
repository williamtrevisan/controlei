<?php

namespace App\Actions\Webhooks;

use App\Actions\CreateSubscription;
use App\Actions\RenewSubscription;
use App\Actions\UpgradeSubscription;
use App\DataTransferObjects\WebhookChargeData;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Plan;
use App\Repositories\Contracts\PaymentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class FulfillSubscriptionPayment
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private CreateSubscription $createSubscription,
        private RenewSubscription $renewSubscription,
        private UpgradeSubscription $upgradeSubscription
    ) {}

    public function execute(WebhookChargeData $data): void
    {
        DB::transaction(function () use ($data) {
            $payment = Payment::query()->where('id', $data->correlationId)->first();
            if (! $payment) {
                Log::warning('Payment not found for charge completed webhook', [
                    'correlation_id' => $data->correlationId,
                ]);

                return;
            }

            if ($payment->status === PaymentStatus::Paid) {
                Log::info('Payment already marked as paid', ['payment_id' => $payment->id]);

                return;
            }

            $this->paymentRepository->update($payment, [
                'status' => PaymentStatus::Paid,
                'paid_at' => $data->paidAt,
            ]);

            match ($data->action) {
                'renew' => $this->renewSubscription->execute($payment->subscription),
                'upgrade' => $this->upgradeSubscription->execute($payment->subscription, $data->plan, $payment->subscription->plan),
                default => $this->createSubscription->execute($payment->user, $payment->subscription->plan),
            };

            Log::info('Charge completed successfully', [
                'payment_id' => $payment->id,
                'subscription_id' => $payment->subscription_id,
                'transaction_id' => $data->transactionId,
                'action' => $data->action,
            ]);
        });
    }
}

