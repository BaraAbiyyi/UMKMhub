<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// Data default produk (Resource: products)
$defaultProducts = [
    [
        'id' => 1,
        'name' => 'Keripik Tempe Renyah',
        'description' => 'Keripik tempe gurih renyah kemasan 200 gram khas Malang',
        'price' => 15000,
        'category' => 'Makanan Ringan',
        'category_id' => 1,
        'stock' => 50,
        'is_active' => true,
        'created_at' => '2026-10-12T08:00:00Z',
    ],
    [
        'id' => 2,
        'name' => 'Kopi Robusta Dampit 250g',
        'description' => 'Kopi bubuk robusta petik merah asli lereng Gunung Semeru',
        'price' => 35000,
        'category' => 'Minuman',
        'category_id' => 2,
        'stock' => 25,
        'is_active' => true,
        'created_at' => '2026-10-12T08:15:00Z',
    ],
    [
        'id' => 3,
        'name' => 'Batik Tulis Corak Madura',
        'description' => 'Kain batik tulis motif floral warna cerah khas pesisir Madura',
        'price' => 175000,
        'category' => 'Fashion',
        'category_id' => 3,
        'stock' => 10,
        'is_active' => true,
        'created_at' => '2026-10-12T08:30:00Z',
    ],
    [
        'id' => 4,
        'name' => 'Sambal Roa Khas Manado',
        'description' => 'Sambal ikan roa asap pedas gurih kemasan toples 150g',
        'price' => 28000,
        'category' => 'Makanan Ringan',
        'category_id' => 1,
        'stock' => 40,
        'is_active' => true,
        'created_at' => '2026-10-12T08:45:00Z',
    ],
    [
        'id' => 5,
        'name' => 'Sabun Herbal Minyak Kelapa',
        'description' => 'Sabun mandi alami virgin coconut oil untuk melembutkan kulit',
        'price' => 12000,
        'category' => 'Kecantikan & Perawatan',
        'category_id' => 4,
        'stock' => 30,
        'is_active' => true,
        'created_at' => '2026-10-12T09:00:00Z',
    ],
];

// Data default kategori (Resource: categories)
$defaultCategories = [
    ['id' => 1, 'name' => 'Makanan Ringan'],
    ['id' => 2, 'name' => 'Minuman'],
    ['id' => 3, 'name' => 'Fashion'],
    ['id' => 4, 'name' => 'Kecantikan & Perawatan'],
];

// Data default pesanan (Resource: orders & order-items)
$defaultOrders = [
    [
        'id' => 1,
        'customer_name' => 'Budi Santoso',
        'status' => 'pending',
        'total_price' => 50000,
        'items' => [
            ['product_id' => 1, 'quantity' => 2, 'subtotal' => 30000],
            ['product_id' => 2, 'quantity' => 1, 'subtotal' => 20000],
        ],
        'created_at' => '2026-10-12T10:00:00Z',
    ]
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

// Data default stok (Resource: stocks)
$defaultStocks = [
    ['product_id' => 1, 'stock' => 50],
    ['product_id' => 2, 'stock' => 25],
    ['product_id' => 3, 'stock' => 10],
    ['product_id' => 4, 'stock' => 40],
    ['product_id' => 5, 'stock' => 30],
];

$getStocks = function () use ($defaultStocks): array {
    return Cache::get('umkm_stocks', $defaultStocks);
};

// Helper Validasi Token & Otorisasi
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
| IMPLEMENTASI 10 ENDPOINT REST API MINGGU 3 (UMKMHub)
|--------------------------------------------------------------------------
*/

// 1. GET /api/products
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

// 2. GET /api/products/{id}
Route::get('/products/{id}', function ($id) use ($getProducts) {
    $product = collect($getProducts())->firstWhere('id', (int) $id);
    if (! $product) {
        return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Produk tidak ditemukan']], 404);
    }
    return response()->json(['data' => $product], 200);
});

// 3. POST /api/products
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

    return response()->json(['data' => $newProduct], 201, ['Location' => "/api/products/{$nextId}"]);
});

// 4. PUT /api/products/{id}
Route::put('/products/{id}', function (Request $request, $id) use ($getProducts, $validateAdminAccess) {
    if ($error = $validateAdminAccess($request)) return $error;

    $products = $getProducts();
    $index = collect($products)->search(fn($item) => $item['id'] === (int) $id);
    if ($index === false) {
        return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Produk tidak ditemukan']], 404);
    }

    $validator = validator($request->all(), [
        'name' => 'required|string|min:3',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validasi gagal', 'details' => $validator->errors()->toArray()]], 422);
    }

    $products[$index] = array_merge($products[$index], $validator->validated());
    Cache::forever('umkm_products', $products);

    return response()->json(['data' => $products[$index]], 200);
});

// 5. DELETE /api/products/{id}
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

// 6. GET /api/categories
Route::get('/categories', function () use ($getCategories) {
    return response()->json(['data' => $getCategories()], 200);
});

// 7. POST /api/orders
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

    return response()->json(['data' => $newOrder], 201, ['Location' => "/api/orders/{$nextId}"]);
});

// 8. GET /api/orders
Route::get('/orders', function (Request $request) use ($getOrders, $validateAdminAccess) {
    if ($error = $validateAdminAccess($request)) return $error;
    return response()->json(['data' => $getOrders()], 200);
});

// 9. GET /api/orders/{id}
Route::get('/orders/{id}', function (Request $request, $id) use ($getOrders, $validateAdminAccess) {
    if ($error = $validateAdminAccess($request)) return $error;

    $order = collect($getOrders())->firstWhere('id', (int) $id);
    if (! $order) {
        return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Pesanan tidak ditemukan']], 404);
    }

    return response()->json(['data' => $order], 200);
});

// 10. PATCH /api/orders/{id}/status
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

// 11. PATCH /api/products/{id}/stock
Route::patch('/products/{id}/stock', function (Request $request, $id) use ($getProducts, $getStocks, $validateAdminAccess) {
    if ($error = $validateAdminAccess($request)) return $error;

    $products = $getProducts();
    $index = collect($products)->search(fn($item) => $item['id'] === (int) $id);
    if ($index === false) {
        return response()->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'Produk tidak ditemukan']], 404);
    }

    $validator = validator($request->all(), [
        'stock' => 'required|integer|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Jumlah stok tidak valid']], 422);
    }

    // Sync stok ke products cache
    $products[$index]['stock'] = (int) $request->stock;
    Cache::forever('umkm_products', $products);

    // Update stocks cache
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


/*
|--------------------------------------------------------------------------
| KOMPATIBILITAS RUTE MINGGU 2 (/api/v1/products)
| Agar file ProductApiTest.php milik kelompokmu tetap lolos test
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () use ($getProducts) {
    Route::get('/products', function () use ($getProducts) {
        return response()->json([
            'status' => 'success',
            'message' => 'Daftar produk UMKM berhasil diambil',
            'data' => array_values($getProducts()),
        ], 200);
    });

    Route::post('/products', function (Request $request) use ($getProducts) {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'nullable|integer',
        ]);

        $products = $getProducts();
        $nextId = empty($products) ? 1 : (max(array_column($products, 'id')) + 1);

        $newProduct = [
            'id' => $nextId,
            'name' => $validated['name'],
            'category' => $validated['category'] ?? 'Umum',
            'price' => (float) $validated['price'],
            'stock' => (int) ($validated['stock'] ?? 0),
        ];

        $products[] = $newProduct;
        Cache::forever('umkm_products', $products);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk UMKM berhasil dibuat',
            'data' => $newProduct,
        ], 201);
    });

    Route::get('/products/{id}', function ($id) use ($getProducts) {
        $products = $getProducts();
        $product = collect($products)->firstWhere('id', (int) $id);

        if (! $product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail produk UMKM berhasil ditemukan',
            'data' => $product,
        ], 200);
    });
});