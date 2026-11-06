<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Admin\StatController;
use App\Http\Controllers\Api\V1\Admin\StoreApplicationController as AdminStoreApplicationController;
use App\Http\Controllers\Api\V1\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\MyStoreController;
use App\Http\Controllers\Api\V1\MyStoreOrderController;
use App\Http\Controllers\Api\V1\MyStoreProductController;
use App\Http\Controllers\Api\V1\MyStoreStockController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PublicStoreController;
use App\Http\Controllers\Api\V1\StoreApplicationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROUTES REST API V1 (UMKMhub)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('v1.')->group(function () {

    // 1. AUTHENTICATION & USER PROFILE
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    });

    // 2. PENDAFTARAN UMKM (publik) & PROFIL TOKO MILIK SENDIRI
    Route::post('/store-applications', [StoreApplicationController::class, 'store'])->name('store-applications.store');

    Route::middleware(['auth:sanctum', 'role:umkm_admin'])->group(function () {
        Route::get('/my-stores', [MyStoreController::class, 'index'])->name('my-stores.index');
        Route::get('/my-stores/{store}', [MyStoreController::class, 'show'])->name('my-stores.show');
        Route::put('/my-stores/{store}', [MyStoreController::class, 'update'])->name('my-stores.update');
        Route::get('/my-stores/{store}/stats', [MyStoreController::class, 'stats'])->name('my-stores.stats');

        // Produk & stok toko sendiri
        Route::get('/my-stores/{store}/products', [MyStoreProductController::class, 'index'])->name('my-stores.products.index');
        Route::post('/my-stores/{store}/products', [MyStoreProductController::class, 'store'])->name('my-stores.products.store');
        Route::put('/my-stores/{store}/products/{product}', [MyStoreProductController::class, 'update'])->name('my-stores.products.update');
        Route::delete('/my-stores/{store}/products/{product}', [MyStoreProductController::class, 'destroy'])->name('my-stores.products.destroy');

        Route::get('/my-stores/{store}/stocks', [MyStoreStockController::class, 'index'])->name('my-stores.stocks.index');
        Route::patch('/my-stores/{store}/stocks/{stock}', [MyStoreStockController::class, 'update'])->name('my-stores.stocks.update');

        // Pesanan masuk toko
        Route::get('/my-stores/{store}/orders', [MyStoreOrderController::class, 'index'])->name('my-stores.orders.index');
        Route::patch('/my-stores/{store}/orders/{order}/status', [MyStoreOrderController::class, 'updateStatus'])->name('my-stores.orders.status');
    });

    // 3. TOKO PUBLIK
    Route::get('/stores', [PublicStoreController::class, 'index'])->name('stores.index');
    Route::get('/stores/{store:slug}', [PublicStoreController::class, 'show'])->name('stores.show');
    Route::get('/stores/{store:slug}/products', [PublicStoreController::class, 'products'])->name('stores.products');

    // 4. KATEGORI
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // 5. KATALOG PRODUK PUBLIK
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // 6. KERANJANG, ALAMAT, PESANAN, PEMBAYARAN SIMULASI (User)
    Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
        Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
        Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
        Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

        Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

        Route::post('/orders/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/simulate-payment', [OrderController::class, 'simulatePayment'])->name('orders.simulate-payment');
    });

    // Detail pesanan: pembeli, pemilik toko, atau superadmin
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });

    // 7. SUPERADMIN
    Route::middleware(['auth:sanctum', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/store-applications', [AdminStoreApplicationController::class, 'index'])->name('store-applications.index');
        Route::post('/stores/{store}/approve', [AdminStoreController::class, 'approve'])->name('stores.approve');
        Route::post('/stores/{store}/reject', [AdminStoreController::class, 'reject'])->name('stores.reject');
        Route::post('/stores/{store}/suspend', [AdminStoreController::class, 'suspend'])->name('stores.suspend');
        Route::post('/stores/{store}/activate', [AdminStoreController::class, 'activate'])->name('stores.activate');
        Route::get('/stores', [AdminStoreController::class, 'index'])->name('stores.index');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/stats', [StatController::class, 'index'])->name('stats.index');
    });
});
