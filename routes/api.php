<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('products', ProductController::class);
    Route::get('/seller/products', [ProductController::class, 'sellerProducts']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{productId}', [CartController::class, 'destroy']);

    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
    Route::get('/seller/orders', [SellerController::class, 'orders']);
    Route::get('/seller/dashboard', [SellerController::class, 'dashboard']);
    Route::apiResource('addresses', AddressController::class);
});

Route::get('/products', [ProductController::class, 'index']);