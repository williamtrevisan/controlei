<?php

namespace App\Http\Controllers;

use App\Actions\Webhooks\AuditSubscriptionPayment;
use App\DataTransferObjects\WebhookChargeData;
use App\DataTransferObjects\WebhookPayerData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChargeCompletedNotSameCustomerPayerWebhookController extends Controller
{
    public function __invoke(Request $request, AuditSubscriptionPayment $auditSubscriptionPayment): Response
    {
        $auditSubscriptionPayment
            ->execute(WebhookPayerData::from($request->all()), WebhookChargeData::from($request->all()));

        return response()->noContent();
    }
}

