<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\PublicController;
use App\Http\Controllers\Web\Superadmin\DashboardController as SuperadminDashboard;
use App\Http\Controllers\Web\Umkm\DashboardController as UmkmDashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROUTES WEB (Blade)
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicController::class, 'home'])->name('home');

// Publik
Route::get('/umkm', [PublicController::class, 'stores'])->name('stores.index');
Route::get('/umkm/{store:slug}', [PublicController::class, 'store'])->name('stores.show');
Route::get('/produk', [PublicController::class, 'products'])->name('products.index');
Route::get('/produk/{product}', [PublicController::class, 'product'])->name('products.show');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
    Route::get('/daftar', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
    Route::get('/daftar-umkm', [AuthController::class, 'storeApplicationForm'])->name('store-applications.create');
    Route::post('/daftar-umkm', [AuthController::class, 'storeApplication'])->name('store-applications.store');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Pembeli
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pesanan/{order}/bayar', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/pesanan/{order}/bayar', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/pesanan/{order}/selesai', [CheckoutController::class, 'done'])->name('checkout.done');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/batal', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/akun', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/akun/alamat', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/akun/alamat/{address}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/akun/alamat/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');
});

// Dashboard Admin UMKM
Route::middleware(['auth', 'role:umkm_admin'])->prefix('dashboard')->name('umkm.')->group(function () {
    Route::get('/', [UmkmDashboard::class, 'index'])->name('dashboard');
    Route::get('/produk', [UmkmDashboard::class, 'products'])->name('products');
    Route::post('/produk', [UmkmDashboard::class, 'storeProduct'])->name('products.store');
    Route::put('/produk/{product}', [UmkmDashboard::class, 'updateProduct'])->name('products.update');
    Route::delete('/produk/{product}', [UmkmDashboard::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/stok', [UmkmDashboard::class, 'stocks'])->name('stocks');
    Route::patch('/stok/{stock}', [UmkmDashboard::class, 'updateStock'])->name('stocks.update');
    Route::get('/pesanan', [UmkmDashboard::class, 'orders'])->name('orders');
    Route::get('/pesanan/{order}', [UmkmDashboard::class, 'orderShow'])->name('orders.show');
    Route::patch('/pesanan/{order}/status', [UmkmDashboard::class, 'updateOrderStatus'])->name('orders.status');
    Route::get('/toko', [UmkmDashboard::class, 'storeProfile'])->name('store');
    Route::put('/toko', [UmkmDashboard::class, 'updateStore'])->name('store.update');
    Route::post('/toko/ajukan-ulang', [UmkmDashboard::class, 'reapply'])->name('store.reapply');
});

// Dashboard Superadmin
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [SuperadminDashboard::class, 'index'])->name('dashboard');
    Route::get('/persetujuan', [SuperadminDashboard::class, 'applications'])->name('applications');
    Route::post('/toko/{store}/setujui', [SuperadminDashboard::class, 'approve'])->name('stores.approve');
    Route::post('/toko/{store}/tolak', [SuperadminDashboard::class, 'reject'])->name('stores.reject');
    Route::get('/toko', [SuperadminDashboard::class, 'stores'])->name('stores');
    Route::post('/toko/{store}/suspend', [SuperadminDashboard::class, 'suspend'])->name('stores.suspend');
    Route::post('/toko/{store}/aktifkan', [SuperadminDashboard::class, 'activate'])->name('stores.activate');
    Route::get('/pengguna', [SuperadminDashboard::class, 'users'])->name('users');
    Route::get('/kategori', [SuperadminDashboard::class, 'categories'])->name('categories');
    Route::post('/kategori', [SuperadminDashboard::class, 'storeCategory'])->name('categories.store');
    Route::put('/kategori/{category}', [SuperadminDashboard::class, 'updateCategory'])->name('categories.update');
    Route::delete('/kategori/{category}', [SuperadminDashboard::class, 'destroyCategory'])->name('categories.destroy');
    Route::get('/pesanan', [SuperadminDashboard::class, 'orders'])->name('orders');
});
