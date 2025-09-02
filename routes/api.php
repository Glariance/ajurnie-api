<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\GoalController;
use App\Http\Controllers\api\SubscriptionController;
use App\Http\Controllers\StripeWebhookController;



// Public routes (no authentication required)
// Route::get('test', [AuthController::class, 'index']);
Route::post('register', [AuthController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
Route::post('store-goal', [GoalController::class, 'store']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/subscription', [SubscriptionController::class, 'show']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
});    



// Development route (remove in production)
Route::get('users', [AuthController::class, 'getUser']);

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
