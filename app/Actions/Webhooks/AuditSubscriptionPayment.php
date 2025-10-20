<?php

namespace App\Actions\Webhooks;

use App\DataTransferObjects\WebhookChargeData;
use App\DataTransferObjects\WebhookPayerData;
use Illuminate\Support\Facades\Log;

readonly class AuditSubscriptionPayment
{
    public function __construct(
        private FulfillSubscriptionPayment $fulfillSubscriptionPayment
    ) {}

    public function execute(WebhookPayerData $payerData, WebhookChargeData $chargeData): void
    {
        Log::warning('Charge completed with different payer', [
            'correlation_id' => $payerData->correlationId,
            'customer_name' => $payerData->customerName,
            'customer_tax_id' => $payerData->customerTaxId,
            'payer_name' => $payerData->payerName,
            'payer_tax_id' => $payerData->payerTaxId,
        ]);

        $this->fulfillSubscriptionPayment->execute($chargeData);
    }
}

