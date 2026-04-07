<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Storefront\CategoryController as StorefrontCategoryController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\Storefront\SearchController;
use Illuminate\Support\Facades\Route;

// Storefront (public)
Route::prefix('v1')->group(function () {
    Route::get('products', [StorefrontProductController::class, 'index']);
    Route::get('products/{slug}', [StorefrontProductController::class, 'show']);

    Route::get('categories', [StorefrontCategoryController::class, 'index']);
    Route::get('categories/{slug}', [StorefrontCategoryController::class, 'show']);

    Route::get('search', SearchController::class);
});

// Admin auth
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:admin-login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);

        Route::get('dashboard', DashboardController::class);

        Route::apiResource('products', ProductController::class);

        Route::post('products/{product}/images', [ProductImageController::class, 'store']);
        Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy']);
        Route::patch('products/{product}/images/reorder', [ProductImageController::class, 'reorder']);

        Route::apiResource('categories', CategoryController::class)->except(['show']);
    });
});
