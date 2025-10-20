<?php

namespace App\Http\Controllers;

use App\Actions\Webhooks\FulfillSubscriptionPayment;
use App\DataTransferObjects\WebhookChargeData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChargeCompletedWebhookController extends Controller
{
    public function __invoke(Request $request, FulfillSubscriptionPayment $fulfillSubscriptionPayment): Response
    {
        $fulfillSubscriptionPayment->execute(WebhookChargeData::from($request->all()));

        return response()->noContent();
    }
}

