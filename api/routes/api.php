<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Storefront\AuthController;
use App\Http\Controllers\Storefront\CategoryController as StorefrontCategoryController;
use App\Http\Controllers\Storefront\PasswordResetController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\Storefront\SearchController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Database unavailable'], 503);
    }
});

// Storefront (public)
Route::prefix('v1')->middleware('throttle:api-public')->group(function () {
    Route::get('products', [StorefrontProductController::class, 'index']);
    Route::get('products/{slug}', [StorefrontProductController::class, 'show']);

    Route::get('categories', [StorefrontCategoryController::class, 'index']);
    Route::get('categories/{slug}', [StorefrontCategoryController::class, 'show']);

    Route::get('search', SearchController::class);

    // User auth
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:api-public');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:user-login');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:api-public');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:api-public');

    // Authenticated user
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'update']);
        Route::put('me/password', [AuthController::class, 'updatePassword']);
        Route::delete('me', [AuthController::class, 'destroy']);
        Route::post('email/verify', [AuthController::class, 'verifyEmail']);
        Route::post('email/resend', [AuthController::class, 'resendVerification']);
    });
});

// Admin auth
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:admin-login');

    Route::middleware(['auth:sanctum', 'throttle:api-auth'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);

        Route::get('dashboard', DashboardController::class);

        Route::apiResource('products', ProductController::class);

        Route::get('media', [ProductImageController::class, 'index']);
        Route::patch('media/{image}', [ProductImageController::class, 'update']);

        Route::post('products/{product}/images', [ProductImageController::class, 'store']);
        Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy']);
        Route::patch('products/{product}/images/reorder', [ProductImageController::class, 'reorder']);

        Route::apiResource('categories', CategoryController::class)->except(['show']);

        Route::get('settings', [SettingController::class, 'index']);
        Route::put('settings', [SettingController::class, 'update']);
    });
});
