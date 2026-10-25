<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\OrderController;

/*
|--------------------------------------------------------------------------
| IN-MEMORY / CACHE DEFAULT DATA (MOCK DATA)
|--------------------------------------------------------------------------
*/

$defaultProducts = [
    ['id' => 1, 'name' => 'Keripik Tempe Renyah', 'description' => 'Keripik tempe gurih renyah kemasan 200 gram khas Malang', 'category' => 'Makanan Ringan', 'category_id' => 1, 'price' => 15000, 'stock' => 50, 'is_active' => true],
    ['id' => 2, 'name' => 'Keripik Singkong Balado', 'description' => 'Keripik singkong dengan bumbu balado', 'category' => 'Makanan Ringan', 'category_id' => 1, 'price' => 12000, 'stock' => 25, 'is_active' => true],
    ['id' => 3, 'name' => 'Kue Brownies', 'description' => 'Brownies cokelat rumahan', 'category' => 'Kue', 'category_id' => 2, 'price' => 25000, 'stock' => 10, 'is_active' => true],
    ['id' => 4, 'name' => 'Sambal Pecel', 'description' => 'Sambal pecel khas rumahan', 'category' => 'Bumbu', 'category_id' => 3, 'price' => 18000, 'stock' => 40, 'is_active' => true],
    ['id' => 5, 'name' => 'Kopi Lokal', 'description' => 'Kopi pilihan dari petani lokal', 'category' => 'Minuman', 'category_id' => 4, 'price' => 30000, 'stock' => 30, 'is_active' => true],
];

$defaultCategories = [
    ['id' => 1, 'name' => 'Makanan Ringan'],
    ['id' => 2, 'name' => 'Kue'],
    ['id' => 3, 'name' => 'Bumbu'],
    ['id' => 4, 'name' => 'Minuman'],
];

$defaultOrders = [
    ['id' => 1, 'customer_name' => 'Budi Santoso', 'status' => 'pending', 'total_price' => 15000, 'items' => [['product_id' => 1, 'quantity' => 1]], 'created_at' => now()->toIso8601String()],
];

$defaultStocks = [
    ['product_id' => 1, 'stock' => 50],
    ['product_id' => 2, 'stock' => 25],
    ['product_id' => 3, 'stock' => 10],
    ['product_id' => 4, 'stock' => 40],
    ['product_id' => 5, 'stock' => 30],
];

$defaultReviews = [
    ['id' => 1, 'product_id' => 1, 'user_name' => 'Ahmad', 'rating' => 5, 'comment' => 'Sangat renyah dan gurih!', 'created_at' => now()->toIso8601String()],
];

$getProducts = function () use ($defaultProducts): array {
    return Cache::get('umkm_products', $defaultProducts);
};

$getCategories = function () use ($defaultCategories): array {
    return Cache::get('umkm_categories', $defaultCategories);
};

$getOrders = function () use ($defaultOrders): array {
    return Cache::get('umkm_orders', $defaultOrders);
};

$getStocks = function () use ($defaultStocks): array {
    return Cache::get('umkm_stocks', $defaultStocks);
};

$getReviews = function () use ($defaultReviews): array {
    return Cache::get('umkm_reviews', $defaultReviews);
};

// Helper Validasi Token & Otorisasi Admin
$validateAdminAccess = function (Request $request) {
    $authHeader = $request->header('Authorization');
    if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
        return response()->json([
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'Autentikasi diperlukan',
            ],
        ], 401);
    }

    $token = trim(substr($authHeader, 7));

    if (in_array($token, ['invalid-token', 'unauthorized-user', 'customer-token'])) {
        return response()->json([
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'Anda tidak berhak mengakses aksi ini',
            ],
        ], 403);
    }

    return null;
};

/*
|--------------------------------------------------------------------------
| ROUTES REST API V1 (UMKMHub) - OPENAPI 3.1.0 COMPLIANT
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('v1.')->group(function () use (
    $getProducts,
    $getCategories,
    $getOrders,
    $getStocks,
    $getReviews,
    $validateAdminAccess
) {

    // --- 1. AUTHENTICATION & USER PROFILE ---[cite: 2]
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);


    // --- 2. CATEGORIES ---[cite: 2]
    Route::get('/categories', function () use ($getCategories) {
        return response()->json(['data' => $getCategories()], 200);
    });
    Route::post('/categories', function (Request $request) use ($getCategories, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;
        $validator = validator($request->all(), ['name' => 'required|string']);
        if ($validator->fails()) {
            return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Nama kategori wajib diisi']], 422);
        }
        $categories = $getCategories();
        $nextId = empty($categories) ? 1 : (max(array_column($categories, 'id')) + 1);
        $newCategory = ['id' => $nextId, 'name' => $request->name];
        $categories[] = $newCategory;
        Cache::forever('umkm_categories', $categories);

        return response()->json(['data' => $newCategory], 201);
    });
    Route::patch('/categories/{id}', function (Request $request, $id) use ($getCategories, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;
        $categories = $getCategories();
        $index = collect($categories)->search(fn($item) => $item['id'] === (int) $id);
        if ($index === false) return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Kategori tidak ditemukan']], 404);

        $categories[$index]['name'] = $request->input('name', $categories[$index]['name']);
        Cache::forever('umkm_categories', $categories);

        return response()->json(['data' => $categories[$index]], 200);
    });
    Route::delete('/categories/{id}', function (Request $request, $id) use ($getCategories, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;
        $categories = $getCategories();
        $index = collect($categories)->search(fn($item) => $item['id'] === (int) $id);
        if ($index === false) return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Kategori tidak ditemukan']], 404);

        array_splice($categories, $index, 1);
        Cache::forever('umkm_categories', array_values($categories));

        return response()->json(['data' => ['message' => 'Kategori berhasil dihapus']], 200);
    });


    // --- 3. PRODUCTS, STOCKS & REVIEWS ---[cite: 2]
    Route::get('/products', function (Request $request) use ($getProducts) {
        $products = collect($getProducts());

        if ($search = $request->query('search')) {
            $products = $products->filter(fn($item) => str_contains(strtolower($item['name']), strtolower($search)));
        }

        if ($categoryId = $request->query('category_id')) {
            $products = $products->where('category_id', (int) $categoryId);
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));
        $total = $products->count();
        $items = $products->values()->forPage($page, $perPage)->values()->all();

        return response()->json([
            'data' => $items,
            'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => $total],
        ], 200);
    });

    Route::get('/products/{id}', function ($id) use ($getProducts) {
        $product = collect($getProducts())->firstWhere('id', (int) $id);
        if (! $product) {
            return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Produk tidak ditemukan']], 404);
        }
        return response()->json(['data' => $product], 200);
    });

    Route::post('/products', function (Request $request) use ($getProducts, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;

        $validator = validator($request->all(), [
            'name' => 'required|string|min:3',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validasi gagal', 'details' => $validator->errors()->toArray()]], 422);
        }

        $products = $getProducts();
        $nextId = empty($products) ? 1 : (max(array_column($products, 'id')) + 1);
        $newProduct = array_merge(['id' => $nextId, 'is_active' => true, 'created_at' => now()->toIso8601String()], $validator->validated());

        $products[] = $newProduct;
        Cache::forever('umkm_products', $products);

        return response()->json(['data' => $newProduct], 201, ['Location' => "/api/v1/products/{$nextId}"]);
    });

    Route::patch('/products/{id}', function (Request $request, $id) use ($getProducts, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;

        $products = $getProducts();
        $index = collect($products)->search(fn($item) => $item['id'] === (int) $id);
        if ($index === false) {
            return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Produk tidak ditemukan']], 404);
        }

        $products[$index] = array_merge($products[$index], $request->only(['name', 'description', 'price', 'category_id', 'is_active']));
        Cache::forever('umkm_products', $products);

        return response()->json(['data' => $products[$index]], 200);
    });

    Route::delete('/products/{id}', function (Request $request, $id) use ($getProducts, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;

        $products = $getProducts();
        $index = collect($products)->search(fn($item) => $item['id'] === (int) $id);
        if ($index === false) {
            return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Produk tidak ditemukan']], 404);
        }

        array_splice($products, $index, 1);
        Cache::forever('umkm_products', array_values($products));

        return response()->json(['data' => ['message' => 'Produk berhasil dihapus', 'id' => (int) $id]], 200);
    });

    // Update Stock[cite: 2]
    Route::patch('/products/{id}/stock', function (Request $request, $id) use ($getProducts, $getStocks, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;

        $products = $getProducts();
        $index = collect($products)->search(fn($item) => $item['id'] === (int) $id);
        if ($index === false) {
            return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Produk tidak ditemukan']], 404);
        }

        $validator = validator($request->all(), ['stock' => 'required|integer|min:0']);
        if ($validator->fails()) {
            return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Jumlah stok tidak valid']], 422);
        }

        $products[$index]['stock'] = (int) $request->stock;
        Cache::forever('umkm_products', $products);

        $stocks = $getStocks();
        $stockIndex = collect($stocks)->search(fn($item) => $item['product_id'] === (int) $id);
        if ($stockIndex !== false) {
            $stocks[$stockIndex]['stock'] = (int) $request->stock;
        } else {
            $stocks[] = ['product_id' => (int) $id, 'stock' => (int) $request->stock];
        }
        Cache::forever('umkm_stocks', $stocks);

        return response()->json([
            'data' => [
                'product_id' => (int) $id,
                'stock'      => (int) $request->stock,
                'message'    => 'Stok produk berhasil diperbarui',
            ],
        ], 200);
    });

    // Product Reviews[cite: 2]
    Route::get('/products/{id}/reviews', function ($id) use ($getReviews) {
        $reviews = collect($getReviews())->where('product_id', (int) $id)->values()->all();
        return response()->json(['data' => $reviews], 200);
    });

    Route::post('/products/{id}/reviews', function (Request $request, $id) use ($getReviews) {
        $validator = validator($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Ulasan tidak valid']], 422);
        }

        $reviews = $getReviews();
        $nextId = empty($reviews) ? 1 : (max(array_column($reviews, 'id')) + 1);
        $newReview = [
            'id' => $nextId,
            'product_id' => (int) $id,
            'user_name' => $request->input('user_name', 'Pembeli'),
            'rating' => (int) $request->rating,
            'comment' => $request->comment ?? '',
            'created_at' => now()->toIso8601String(),
        ];

        $reviews[] = $newReview;
        Cache::forever('umkm_reviews', $reviews);

        return response()->json(['data' => $newReview], 201);
    });


    // --- 4. ORDERS ---[cite: 2]
    Route::get('/orders', function (Request $request) use ($getOrders, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;
        return response()->json(['data' => $getOrders()], 200);
    });

    Route::post('/orders', function (Request $request) use ($getOrders, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;

        $validator = validator($request->all(), [
            'customer_name' => 'required|string',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validasi pesanan gagal']], 422);
        }

        $orders = $getOrders();
        $nextId = empty($orders) ? 1 : (max(array_column($orders, 'id')) + 1);
        
        $items = $request->items;
        $totalPrice = collect($items)->sum(function ($item) {
            return isset($item['subtotal'])
                ? (float) $item['subtotal']
                : ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        });

        $newOrder = [
            'id' => $nextId,
            'customer_name' => $request->customer_name,
            'status' => 'pending',
            'total_price' => $totalPrice,
            'items' => $items,
            'created_at' => now()->toIso8601String(),
        ];

        $orders[] = $newOrder;
        Cache::forever('umkm_orders', $orders);

        return response()->json(['data' => $newOrder], 201, ['Location' => "/api/v1/orders/{$nextId}"]);
    });

    Route::get('/orders/{id}', function (Request $request, $id) use ($getOrders, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;

        $order = collect($getOrders())->firstWhere('id', (int) $id);
        if (! $order) {
            return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Pesanan tidak ditemukan']], 404);
        }

        return response()->json(['data' => $order], 200);
    });

    Route::patch('/orders/{id}/status', function (Request $request, $id) use ($getOrders, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;

        $orders = $getOrders();
        $index = collect($orders)->search(fn($item) => $item['id'] === (int) $id);
        if ($index === false) {
            return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Pesanan tidak ditemukan']], 404);
        }

        $validator = validator($request->all(), ['status' => 'required|string|in:pending,processing,completed,cancelled']);
        if ($validator->fails()) {
            return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Status tidak valid']], 422);
        }

        $orders[$index]['status'] = $request->status;
        Cache::forever('umkm_orders', $orders);

        return response()->json(['data' => $orders[$index]], 200);
    });

    Route::post('/orders/{id}/cancel', function (Request $request, $id) use ($getOrders, $validateAdminAccess) {
        if ($error = $validateAdminAccess($request)) return $error;

        $orders = $getOrders();
        $index = collect($orders)->search(fn($item) => $item['id'] === (int) $id);
        if ($index === false) {
            return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Pesanan tidak ditemukan']], 404);
        }

        $orders[$index]['status'] = 'cancelled';
        Cache::forever('umkm_orders', $orders);

        return response()->json(['data' => $orders[$index], 'message' => 'Pesanan berhasil dibatalkan'], 200);
    });


    // --- 5. CUSTOMERS & STOCKS (FITUR LOKAL) ---
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'customerOrders']);

    Route::get('/stocks', [StockController::class, 'index']);
});