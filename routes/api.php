<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function(){

    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/latest', [ProductController::class, 'latest']);
    Route::get('products/popular', [ProductController::class, 'popular']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    
    // Brands & Categories
    Route::get('brands', [BrandController::class, 'index']);
    Route::get('brands/{brand}/products', [BrandController::class, 'products']);


    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}/products', [CategoryController::class, 'products']);


    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist/add', [WishlistController::class, 'add']);
    Route::delete('wishlist/remove/{productId}', [WishlistController::class, 'remove']);
    Route::get('wishlist/check/{productId}', [WishlistController::class, 'check']);



    Route::get('reviews', [ReviewController::class, 'index']);
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::get('my-reviews', [ReviewController::class, 'myReviews']);
    Route::delete('remove', [ReviewController::class, 'remove']);


    // payment gateway route

    Route::post('checkout', [PaymentController::class, 'checkout']);
    
    
    Route::post('sslcommerz/success', [PaymentController::class, 'paymentSuccess'])->name('sslc.success');
    Route::post('sslcommerz/failure', [PaymentController::class, 'paymentFailure'])->name('sslc.failure');
    Route::post('sslcommerz/cancel', [PaymentController::class, 'paymentCancel'])->name('sslc.cancel');
    Route::post('sslcommerz/ipn', [PaymentController::class, 'paymentIpn'])->name('sslc.ipn');


});