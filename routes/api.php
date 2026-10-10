<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// Data default katalog produk UMKM (sesuai spesifikasi atribut PRD Minggu 3)
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

// Helper untuk mengambil list produk dari Cache
$getProducts = function () use ($defaultProducts): array {
    return Cache::get('umkm_products', $defaultProducts);
};

/*
|--------------------------------------------------------------------------
| TUGAS MINGGU 3 - REST API DESIGN (Bara Abiyyi Muslimin)
| Endpoint: /api/products
| Konvensi: Prefix /api, Response {"data": ...}, Error {"error": ...}
|--------------------------------------------------------------------------
*/

// 1. GET /api/products - Menampilkan daftar produk dengan filter query & paginasi
Route::get('/products', function (Request $request) use ($getProducts) {
    $products = collect($getProducts());

    // Filter pencarian (?search=...)
    if ($search = $request->query('search')) {
        $products = $products->filter(function ($item) use ($search) {
            return str_contains(strtolower($item['name']), strtolower($search))
                || str_contains(strtolower($item['description'] ?? ''), strtolower($search));
        });
    }

    // Filter kategori (?category_id=...)
    if ($categoryId = $request->query('category_id')) {
        $products = $products->where('category_id', (int) $categoryId);
    }

    // Paginasi (?page=..., ?per_page=...)
    $page = max(1, (int) $request->query('page', 1));
    $perPage = max(1, (int) $request->query('per_page', 10));
    $total = $products->count();
    $items = $products->values()->forPage($page, $perPage)->values()->all();

    return response()->json([
        'data' => $items,
        'meta' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ],
    ], 200);
});

// 2. POST /api/products - Menambah produk baru ke katalog (Spesifikasi C1 Bara Abiyyi)
Route::post('/products', function (Request $request) use ($getProducts) {
    // A. Cek Format JSON rusak / invalid (400 Bad Request)
    if ($request->header('Content-Type') === 'application/json' && empty($request->json()->all()) && ! empty($request->getContent())) {
        return response()->json([
            'error' => [
                'code' => 'BAD_REQUEST',
                'message' => 'Format JSON tidak valid',
            ],
        ], 400);
    }

    // B. Cek Autentikasi Bearer Token (401 Unauthorized)
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

    // C. Cek Hak Akses / Otorisasi Admin (403 Forbidden)
    if (in_array($token, ['invalid-token', 'unauthorized-user', 'customer-token'])) {
        return response()->json([
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'Anda tidak berhak menambah produk',
            ],
        ], 403);
    }

    // D. Validasi Field (422 Unprocessable Content)
    $validator = validator($request->all(), [
        'name' => 'required|string|min:3',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|integer|min:1',
    ], [
        'name.required' => 'Nama produk wajib diisi',
        'name.min' => 'Nama produk minimal 3 karakter',
        'price.required' => 'Harga produk wajib diisi',
        'price.min' => 'Harga produk harus bernilai positif',
        'category_id.required' => 'Kategori produk wajib diisi',
        'category_id.integer' => 'Kategori yang dipilih tidak valid',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Validasi gagal',
                'details' => $validator->errors()->toArray(),
            ],
        ], 422);
    }

    $validated = $validator->validated();
    $products = $getProducts();
    $nextId = empty($products) ? 1 : (max(array_column($products, 'id')) + 1);

    $newProduct = [
        'id' => $nextId,
        'name' => $validated['name'],
        'description' => $validated['description'] ?? '',
        'price' => (float) $validated['price'],
        'category_id' => (int) $validated['category_id'],
        'is_active' => true,
        'created_at' => now()->toIso8601String(),
    ];

    $products[] = $newProduct;
    Cache::forever('umkm_products', $products);

    return response()->json([
        'data' => $newProduct,
    ], 201, [
        'Location' => "/api/products/{$nextId}",
    ]);
});

// 3. GET /api/products/{id} - Menampilkan detail produk
Route::get('/products/{id}', function ($id) use ($getProducts) {
    $products = $getProducts();
    $product = collect($products)->firstWhere('id', (int) $id);

    if (! $product) {
        return response()->json([
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Produk tidak ditemukan',
            ],
        ], 404);
    }

    return response()->json([
        'data' => $product,
    ], 200);
});

/*
|--------------------------------------------------------------------------
| TUGAS MINGGU 3 - REST API DESIGN (Afsani Wahyu Mawardi)
| Endpoint: /api/categories
| Resource: Categories – pengelompokan produk UMKM
| Atribut: id, name | Relasi: punya banyak Products
|--------------------------------------------------------------------------
*/

// Data default kategori
$defaultCategories = [
    ['id' => 1, 'name' => 'Makanan Ringan'],
    ['id' => 2, 'name' => 'Minuman'],
    ['id' => 3, 'name' => 'Fashion'],
    ['id' => 4, 'name' => 'Kecantikan & Perawatan'],
    ['id' => 5, 'name' => 'Kerajinan'],
];

// Helper untuk mengambil list kategori dari Cache
$getCategories = function () use ($defaultCategories): array {
    return Cache::get('umkm_categories', $defaultCategories);
};

// 3. GET /api/categories – Menampilkan semua kategori produk (Afsani)
Route::get('/categories', function () use ($getCategories) {
    return response()->json([
        'data' => array_values($getCategories()),
    ], 200);
});

// 4. POST /api/categories – Menambah kategori baru (Afsani – Bagian C2)
Route::post('/categories', function (Request $request) use ($getCategories) {
    // A. Cek Format JSON rusak (400 Bad Request)
    if ($request->header('Content-Type') === 'application/json' && empty($request->json()->all()) && ! empty($request->getContent())) {
        return response()->json([
            'error' => [
                'code'    => 'BAD_REQUEST',
                'message' => 'Format JSON tidak valid',
            ],
        ], 400);
    }

    // B. Cek Autentikasi Bearer Token (401 Unauthorized)
    $authHeader = $request->header('Authorization');
    if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
        return response()->json([
            'error' => [
                'code'    => 'UNAUTHORIZED',
                'message' => 'Autentikasi diperlukan',
            ],
        ], 401);
    }

    // C. Validasi field – name wajib diisi (422 Unprocessable Content)
    $validator = validator($request->all(), [
        'name' => 'required|string|min:2',
    ], [
        'name.required' => 'Nama kategori wajib diisi',
        'name.min'      => 'Nama kategori minimal 2 karakter',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => [
                'code'    => 'VALIDATION_ERROR',
                'message' => 'Validasi gagal',
                'details' => $validator->errors()->toArray(),
            ],
        ], 422);
    }

    $categories = $getCategories();
    $newName    = trim($request->input('name'));

    // D. Cek duplikat nama kategori (409 Conflict)
    $isDuplicate = collect($categories)->contains(function ($cat) use ($newName) {
        return strtolower($cat['name']) === strtolower($newName);
    });

    if ($isDuplicate) {
        return response()->json([
            'error' => [
                'code'    => 'CONFLICT',
                'message' => 'Kategori sudah terdaftar',
            ],
        ], 409);
    }

    // E. Simpan kategori baru
    $nextId      = empty($categories) ? 1 : (max(array_column($categories, 'id')) + 1);
    $newCategory = ['id' => $nextId, 'name' => $newName];

    $categories[] = $newCategory;
    Cache::forever('umkm_categories', $categories);

    return response()->json([
        'data' => $newCategory,
    ], 201, [
        'Location' => "/api/categories/{$nextId}",
    ]);
});

/*
|--------------------------------------------------------------------------
| TUGAS MINGGU 2 (KOMPATIBILITAS SEBELUMNYA)
| Endpoint: /api/v1/products
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
