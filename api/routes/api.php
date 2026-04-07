<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\GiftCardController as AdminGiftCardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LoyaltyController;
use App\Http\Controllers\Admin\AbandonedCartController as AdminAbandonedCartController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ShippingController as AdminShippingController;
use App\Http\Controllers\Admin\StoreCreditController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Admin\CallbackRequestController as AdminCallbackController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\StockNotificationController as AdminStockNotificationController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Storefront\AddressController;
use App\Http\Controllers\Storefront\AuthController;
use App\Http\Controllers\Storefront\CategoryController as StorefrontCategoryController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\PasswordResetController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\Storefront\BlogController;
use App\Http\Controllers\Storefront\CallbackRequestController;
use App\Http\Controllers\Storefront\CouponController as StorefrontCouponController;
use App\Http\Controllers\Storefront\GiftCardController;
use App\Http\Controllers\Storefront\PageController as StorefrontPageController;
use App\Http\Controllers\Storefront\AbandonedCartController;
use App\Http\Controllers\Storefront\PaymentController;
use App\Http\Controllers\Storefront\ShippingController as StorefrontShippingController;
use App\Http\Controllers\Storefront\NewsletterController;
use App\Http\Controllers\Storefront\StockNotificationController;
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

    Route::post('products/{product}/notify-me', [StockNotificationController::class, 'store']);
    Route::post('callback-request', [CallbackRequestController::class, 'store']);
    Route::post('coupon/validate', [StorefrontCouponController::class, 'validate']);
    Route::post('shipping/methods', [StorefrontShippingController::class, 'methods']);
    Route::get('payment-methods', [PaymentController::class, 'methods']);
    Route::post('gift-card/check', [GiftCardController::class, 'check']);
    Route::get('pages/{slug}', [StorefrontPageController::class, 'show']);
    Route::post('abandoned-cart', [AbandonedCartController::class, 'store']);
    Route::get('abandoned-cart/recover/{token}', [AbandonedCartController::class, 'recover']);
    Route::post('abandoned-cart/recovered/{token}', [AbandonedCartController::class, 'markRecovered']);

    Route::get('blog', [BlogController::class, 'index']);
    Route::get('blog/sidebar', [BlogController::class, 'sidebar']);
    Route::get('blog/category/{slug}', [BlogController::class, 'byCategory']);
    Route::get('blog/tag/{slug}', [BlogController::class, 'byTag']);
    Route::get('blog/{slug}', [BlogController::class, 'show']);

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

        Route::get('products/{product}/variants', [VariantController::class, 'index']);
        Route::post('products/{product}/variants', [VariantController::class, 'store']);
        Route::put('products/{product}/variants/bulk', [VariantController::class, 'bulkUpdate']);
        Route::put('variants/{variant}', [VariantController::class, 'update']);
        Route::delete('variants/{variant}', [VariantController::class, 'destroy']);

        Route::get('attributes', [AttributeController::class, 'index']);
        Route::post('attributes', [AttributeController::class, 'store']);
        Route::put('attributes/{attribute}', [AttributeController::class, 'update']);
        Route::delete('attributes/{attribute}', [AttributeController::class, 'destroy']);
        Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue']);
        Route::put('attribute-values/{value}', [AttributeController::class, 'updateValue']);
        Route::delete('attribute-values/{value}', [AttributeController::class, 'destroyValue']);
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

        Route::get('posts', [AdminBlogController::class, 'index']);
        Route::post('posts', [AdminBlogController::class, 'store']);
        Route::get('posts/{post}', [AdminBlogController::class, 'show']);
        Route::put('posts/{post}', [AdminBlogController::class, 'update']);
        Route::delete('posts/{post}', [AdminBlogController::class, 'destroy']);

        Route::get('blog-categories', [AdminBlogController::class, 'categories']);
        Route::post('blog-categories', [AdminBlogController::class, 'storeCategory']);
        Route::put('blog-categories/{category}', [AdminBlogController::class, 'updateCategory']);
        Route::delete('blog-categories/{category}', [AdminBlogController::class, 'destroyCategory']);

        Route::get('tags', [AdminBlogController::class, 'tags']);
        Route::post('tags', [AdminBlogController::class, 'storeTag']);
        Route::delete('tags/{tag}', [AdminBlogController::class, 'destroyTag']);

        Route::apiResource('gift-cards', AdminGiftCardController::class)->except(['destroy']);
        Route::post('gift-cards/{giftCard}/adjust', [AdminGiftCardController::class, 'adjust']);

        Route::get('loyalty', [LoyaltyController::class, 'index']);
        Route::get('loyalty/{account}', [LoyaltyController::class, 'show']);
        Route::post('loyalty/{account}/adjust', [LoyaltyController::class, 'adjust']);
        Route::get('loyalty-config', [LoyaltyController::class, 'config']);

        Route::get('store-credits', [StoreCreditController::class, 'index']);
        Route::get('store-credits/{account}', [StoreCreditController::class, 'show']);
        Route::post('store-credits/user/{user}/adjust', [StoreCreditController::class, 'adjust']);

        Route::apiResource('pages', AdminPageController::class);

        Route::get('admins', [AdminUserController::class, 'index']);
        Route::post('admins', [AdminUserController::class, 'store']);
        Route::put('admins/{admin}', [AdminUserController::class, 'update']);
        Route::delete('admins/{admin}', [AdminUserController::class, 'destroy']);

        Route::get('activity-log', [ActivityLogController::class, 'index']);

        Route::get('webhooks', [WebhookController::class, 'index']);
        Route::post('webhooks', [WebhookController::class, 'store']);
        Route::put('webhooks/{webhook}', [WebhookController::class, 'update']);
        Route::delete('webhooks/{webhook}', [WebhookController::class, 'destroy']);
        Route::get('webhooks/{webhook}/logs', [WebhookController::class, 'logs']);
        Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test']);

        Route::get('abandoned-carts', [AdminAbandonedCartController::class, 'index']);
        Route::get('abandoned-carts/stats', [AdminAbandonedCartController::class, 'stats']);

        Route::get('export/products', [ExportController::class, 'products']);
        Route::get('export/orders', [ExportController::class, 'orders']);
        Route::get('export/customers', [ExportController::class, 'customers']);
        Route::get('export/product-template', [ExportController::class, 'productTemplate']);
        Route::post('import/products', [ImportController::class, 'products']);

        Route::apiResource('payment-methods', PaymentMethodController::class)->except(['show']);
        Route::get('payments', [PaymentMethodController::class, 'transactions']);

        Route::apiResource('tax-rates', TaxRateController::class)->except(['show']);

        Route::get('shipping-zones', [AdminShippingController::class, 'zones']);
        Route::post('shipping-zones', [AdminShippingController::class, 'storeZone']);
        Route::put('shipping-zones/{zone}', [AdminShippingController::class, 'updateZone']);
        Route::delete('shipping-zones/{zone}', [AdminShippingController::class, 'destroyZone']);
        Route::post('shipping-zones/{zone}/methods', [AdminShippingController::class, 'storeMethod']);
        Route::put('shipping-methods/{method}', [AdminShippingController::class, 'updateMethod']);
        Route::delete('shipping-methods/{method}', [AdminShippingController::class, 'destroyMethod']);

        Route::get('inventory', [InventoryController::class, 'index']);
        Route::post('inventory/{product}/adjust', [InventoryController::class, 'adjust']);
        Route::get('inventory/{product}/history', [InventoryController::class, 'history']);
        Route::post('inventory/bulk-adjust', [InventoryController::class, 'bulkAdjust']);
        Route::get('inventory/export', [InventoryController::class, 'export']);
        Route::post('inventory/import', [InventoryController::class, 'import']);

        Route::apiResource('coupons', CouponController::class);
        Route::post('coupons/bulk-generate', [CouponController::class, 'bulkGenerate']);
        Route::get('coupons/{coupon}/stats', [CouponController::class, 'stats']);

        Route::get('callback-requests', [AdminCallbackController::class, 'index']);
        Route::patch('callback-requests/{callbackRequest}', [AdminCallbackController::class, 'updateStatus']);

        Route::get('stock-notifications', [AdminStockNotificationController::class, 'index']);
        Route::get('stock-notifications/product/{product}', [AdminStockNotificationController::class, 'byProduct']);

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
