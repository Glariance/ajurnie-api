<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\GoalController;
use App\Http\Controllers\api\SubscriptionController;
use App\Http\Controllers\StripeWebhookController;


Route::post('/ping', function (Request $request) {
    return response()->json(['message' => 'pong']);
});

Route::post('register', [AuthController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password',  [AuthController::class, 'resetPassword']);

// Protected routes (authentication required)

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('user', [AuthController::class, 'getUser'])->name('user.show');
    Route::post('update-user', [AuthController::class, 'updateUser'])->name('user.update');

    // optional: keep old alias but point to same method (if something in FE still calls it)
    // Route::post('update-profile', [AuthController::class, 'updateUser'])->name('user.update-legacy');

    Route::post('store-goal', [GoalController::class, 'store']);
    Route::get('subscription', [SubscriptionController::class, 'show']);
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('change-plan', [SubscriptionController::class, 'changePlan']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
});

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
