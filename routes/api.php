<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\PushSubscriptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Feature Flag API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/features', [FeatureController::class, 'index']);
    Route::get('/features/{feature}/check', [FeatureController::class, 'check']);
    Route::get('/features/limits', [FeatureController::class, 'limits']);
});

// Device Token Management
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::get('/device-tokens', [DeviceTokenController::class, 'index']);
    Route::patch('/device-tokens/{token}', [DeviceTokenController::class, 'update']);
    Route::delete('/device-tokens/{token}', [DeviceTokenController::class, 'destroy']);
    Route::post('/device-tokens/{token}/test', [DeviceTokenController::class, 'test']);
});

// Push Notifications
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::get('/notifications/history', [NotificationController::class, 'history']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
});

// Push Subscriptions
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store']);
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy']);
    Route::get('/push-subscriptions', [PushSubscriptionController::class, 'show']);
});

// Admin-only notification routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/notifications/broadcast', [NotificationController::class, 'broadcast']);
    Route::post('/notifications/send-to-user', [NotificationController::class, 'sendToUser']);
    Route::post('/notifications/send-to-clinic', [NotificationController::class, 'sendToClinic']);
});