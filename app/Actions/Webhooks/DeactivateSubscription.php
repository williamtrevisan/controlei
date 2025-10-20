<?php

namespace App\Actions\Webhooks;

use App\Actions\ExpireSubscription;
use App\DataTransferObjects\WebhookChargeData;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class DeactivateSubscription
{
    public function __construct(
        private UpdatePaymentAsExpired $updatePaymentAsExpired,
        private ExpireSubscription $expireSubscription
    ) {}

    public function execute(WebhookChargeData $data): void
    {
        DB::transaction(function () use ($data) {
            $payment = Payment::query()
                ->where('id', $data->correlationId)
                ->first();
            if (! $payment) {
                Log::warning('Payment not found for charge expired webhook', [
                    'correlation_id' => $data->correlationId,
                ]);

                return;
            }

            if ($payment->status === PaymentStatus::Expired) {
                Log::info('Payment already marked as expired', ['payment_id' => $payment->id]);

                return;
            }

            $this->updatePaymentAsExpired->execute($payment);

            $this->expireSubscription->execute($payment->subscription);

            Log::info('Charge expired successfully', [
                'payment_id' => $payment->id,
                'subscription_id' => $payment->subscription_id,
            ]);
        });
    }
}

