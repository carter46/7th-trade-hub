<?php

/**
 * Example snippet for routes/api.php (Laravel).
 * Copy the relevant parts into your real routes/api.php once Laravel is installed.
 * Prefix for these routes will be /api (e.g. /api/transactions).
 */

use Illuminate\Support\Facades\Route;

// Optional: apply auth:sanctum or auth:api middleware for protected endpoints
// Route::middleware('auth:sanctum')->group(function () {

Route::get('/transactions', function () {
    return response()->json(['message' => 'List user transactions (implement with controller)']);
});
Route::get('/messages', function () {
    return response()->json(['message' => 'List messages (implement with controller)']);
});
Route::get('/notifications', function () {
    return response()->json(['message' => 'List notifications (implement with controller)']);
});

// });
