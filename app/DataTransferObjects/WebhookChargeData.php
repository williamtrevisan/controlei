<?php

namespace App\DataTransferObjects;

readonly class WebhookChargeData
{
    public function __construct(
        public string $correlationId,
        public string $paidAt,
        public ?string $action,
        public ?string $oldPlanId,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            correlationId: Arr::get($charge, 'charge.correlationID'),
            paidAt: Arr::get($charge, 'charge.paidAt', now()->toIso8601String()),
            action: Arr::get($charge, 'charge.additionalInfo.action'),
            oldPlanId: Arr::get($charge, 'charge.additionalInfo.old_plan_id'),
        );
    }
}

