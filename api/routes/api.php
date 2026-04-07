<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Storefront\AddressController;
use App\Http\Controllers\Storefront\AuthController;
use App\Http\Controllers\Storefront\CategoryController as StorefrontCategoryController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\PasswordResetController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\Storefront\NewsletterController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\WishlistController;
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

    Route::get('products/{product}/reviews', [ReviewController::class, 'index']);

    Route::post('newsletter/subscribe', [NewsletterController::class, 'subscribe']);
    Route::get('newsletter/confirm/{token}', [NewsletterController::class, 'confirm']);
    Route::get('newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe']);

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

        Route::put('me/newsletter', [AuthController::class, 'updateNewsletter']);
        Route::apiResource('addresses', AddressController::class)->except(['show']);

        Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
        Route::post('reviews/{review}/helpful', [ReviewController::class, 'helpful']);

        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::get('wishlist/ids', [WishlistController::class, 'ids']);
        Route::post('wishlist/sync', [WishlistController::class, 'sync']);
        Route::post('wishlist/{product}', [WishlistController::class, 'store']);
        Route::delete('wishlist/{product}', [WishlistController::class, 'destroy']);

        Route::get('orders', [AuthController::class, 'orders']);
        Route::get('orders/{orderNumber}', [AuthController::class, 'showOrder']);
    });

    // Checkout (guest ili auth)
    Route::post('checkout', [CheckoutController::class, 'store']);
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
        Route::get('products/{product}/relations', [ProductController::class, 'relations']);
        Route::put('products/{product}/relations', [ProductController::class, 'updateRelations']);

        Route::get('media', [ProductImageController::class, 'index']);
        Route::patch('media/{image}', [ProductImageController::class, 'update']);

        Route::post('products/{product}/images', [ProductImageController::class, 'store']);
        Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy']);
        Route::patch('products/{product}/images/reorder', [ProductImageController::class, 'reorder']);

        Route::apiResource('categories', CategoryController::class)->except(['show']);

        Route::get('settings', [SettingController::class, 'index']);
        Route::put('settings', [SettingController::class, 'update']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::put('orders/{order}', [OrderController::class, 'update']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::patch('orders/{order}/tracking', [OrderController::class, 'updateTracking']);
        Route::post('orders/{order}/refund', [OrderController::class, 'refund']);

        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('customers/{customer}', [CustomerController::class, 'show']);

        Route::get('newsletter', [AdminNewsletterController::class, 'index']);
        Route::get('newsletter/stats', [AdminNewsletterController::class, 'stats']);
        Route::get('newsletter/export', [AdminNewsletterController::class, 'export']);
        Route::delete('newsletter/{subscriber}', [AdminNewsletterController::class, 'destroy']);

        Route::get('reviews', [AdminReviewController::class, 'index']);
        Route::patch('reviews/{review}/approve', [AdminReviewController::class, 'approve']);
        Route::patch('reviews/{review}/reject', [AdminReviewController::class, 'reject']);
        Route::post('reviews/{review}/reply', [AdminReviewController::class, 'reply']);
    });
});
