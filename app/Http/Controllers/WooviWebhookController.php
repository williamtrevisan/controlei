<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WooviWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        try {
            $this->handleWebhook($request->all());

            return response()->noContent();
        } catch (\Exception $e) {
            Log::error('Woovi webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    private function handleWebhook(array $payload): void
    {
    }
}

