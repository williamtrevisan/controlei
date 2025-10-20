<?php

namespace App\Http\Controllers;

use App\Actions\Webhooks\DeactivateSubscription;
use App\DataTransferObjects\WebhookChargeData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChargeExpiredWebhookController extends Controller
{
    public function __invoke(Request $request, DeactivateSubscription $deactivateSubscription): Response
    {
        $deactivateSubscription->execute(WebhookChargeData::from($request->all()));

        return response()->noContent();
    }
}

