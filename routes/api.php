<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\OrderController;

Route::prefix('v1')->name('v1.')->group(function () {
    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Products (CRUD)
    Route::apiResource('products', ProductController::class);

    // Stocks
    Route::get('/stocks', [StockController::class, 'index']);
    Route::patch('/stocks/{stock}', [StockController::class, 'update']);

    // Customers
    Route::apiResource('customers', CustomerController::class)->except(['update', 'destroy']);
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'customerOrders']);

    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
});