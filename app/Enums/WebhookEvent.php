<?php

namespace App\Enums;

enum WebhookEvent: string
{
    case ChargeCompleted = 'OPENPIX:CHARGE_COMPLETED';
    case ChargeCompletedNotSameCustomerPayer = 'OPENPIX:CHARGE_COMPLETED_NOT_SAME_CUSTOMER_PAYER';
    case ChargeExpired = 'OPENPIX:CHARGE_EXPIRED';
}

