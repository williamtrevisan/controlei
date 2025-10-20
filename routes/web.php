<?php

use App\Http\Controllers\AcceptInviteController;
use App\Http\Controllers\ChargeCompletedNotSameCustomerPayerWebhookController;
use App\Http\Controllers\ChargeCompletedWebhookController;
use App\Http\Controllers\ChargeExpiredWebhookController;
use App\Http\Controllers\RejectInviteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhooks/charge-completed', ChargeCompletedWebhookController::class)->name('webhooks.charge-completed');
Route::post('/webhooks/charge-completed-not-same-customer-payer', ChargeCompletedNotSameCustomerPayerWebhookController::class)->name('webhooks.charge-completed-not-same-customer-payer');
Route::post('/webhooks/charge-expired', ChargeExpiredWebhookController::class)->name('webhooks.charge-expired');

Route::middleware(['auth'])->group(function () {
    Route::post('/invites/{invite}/accept', AcceptInviteController::class)->name('invites.accept');
    Route::post('/invites/{invite}/reject', RejectInviteController::class)->name('invites.reject');
});
