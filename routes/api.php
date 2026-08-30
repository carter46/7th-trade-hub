<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SiteIntegrations\SiteIntegrationApiController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
});

Route::prefix('site-integrations/v1')->middleware('throttle:60,1')->group(function () {
    Route::post('/demo/tokens/validate', [SiteIntegrationApiController::class, 'validateToken']);
    Route::get('/subscription', [SiteIntegrationApiController::class, 'subscriptionStatus']);
});
