<?php

use App\Http\Controllers\AcceptInviteController;
use App\Http\Controllers\RejectInviteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/invites/{invite}/accept', AcceptInviteController::class)->name('invites.accept');
    Route::post('/invites/{invite}/reject', RejectInviteController::class)->name('invites.reject');
});
