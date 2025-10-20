<?php

namespace App\DataTransferObjects;

readonly class WebhookPayerData
{
    public function __construct(
        public string $correlationId,
        public ?string $customerName,
        public ?string $customerTaxId,
        public ?string $payerName,
        public ?string $payerTaxId,
    ) {}

    public static function from(array $data): self
    {
        $charge = $data['charge'] ?? [];
        $payer = $data['payer'] ?? [];
        $customer = $charge['customer'] ?? [];

        return new self(
            correlationId: $charge['correlationID'] ?? '',
            customerName: $customer['name'] ?? null,
            customerTaxId: $customer['taxID']['taxID'] ?? null,
            payerName: $payer['name'] ?? null,
            payerTaxId: $payer['taxID']['taxID'] ?? null,
        );
    }
}

