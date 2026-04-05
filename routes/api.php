<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
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


});