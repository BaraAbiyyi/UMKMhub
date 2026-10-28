<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\StockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROUTES REST API V1 (UMKMHub) - OPENAPI 3.1.0 & LAYERED ARCHITECTURE
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('v1.')->group(function () {

    // 1. AUTHENTICATION & USER PROFILE
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');

    // 2. CATEGORIES (CRUD Master Data)
    Route::apiResource('categories', CategoryController::class);

    // 3. PRODUCTS (CRUD Katalog & Koleksi)
    Route::apiResource('products', ProductController::class);

    // 4. STOCKS (Monitoring & Optimistic Concurrency Control)
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::patch('/stocks/{stock}', [StockController::class, 'update'])->name('stocks.update');

    // 5. CUSTOMERS & CUSTOMER ORDERS (Nested Resource)
    Route::apiResource('customers', CustomerController::class)->except(['update', 'destroy']);
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'customerOrders'])->name('customers.orders');

    // 6. ORDERS (Checkout & Status Lifecycle)
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
});
