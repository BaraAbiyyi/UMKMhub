<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// Data default katalog produk UMKM
$defaultProducts = [
    [
        'id' => 1,
        'name' => 'Keripik Tempe Renyah',
        'category' => 'Makanan Ringan',
        'price' => 15000,
        'stock' => 50,
    ],
    [
        'id' => 2,
        'name' => 'Kopi Robusta Dampit 250g',
        'category' => 'Minuman',
        'price' => 35000,
        'stock' => 25,
    ],
    [
        'id' => 3,
        'name' => 'Batik Tulis Corak Madura',
        'category' => 'Fashion',
        'price' => 175000,
        'stock' => 10,
    ],
    [
        'id' => 4,
        'name' => 'Sambal Roa Khas Manado',
        'category' => 'Makanan Ringan',
        'price' => 28000,
        'stock' => 40,
    ],
    [
        'id' => 5,
        'name' => 'Sabun Herbal Minyak Kelapa',
        'category' => 'Kecantikan & Perawatan',
        'price' => 12000,
        'stock' => 30,
    ],
];

// Helper untuk mengambil list produk (menggunakan Cache agar data persisten antar-request)
$getProducts = function () use ($defaultProducts): array {
    return Cache::get('umkm_products', $defaultProducts);
};

Route::prefix('v1')->group(function () use ($getProducts) {
    /**
     * 1. GET /api/v1/products
     * Mengembalikan daftar semua produk UMKM
     */
    Route::get('/products', function () use ($getProducts) {
        return response()->json([
            'status' => 'success',
            'message' => 'Daftar produk UMKM berhasil diambil',
            'data' => array_values($getProducts()),
        ], 200);
    });

    /**
     * 2. POST /api/v1/products
     * Menerima payload JSON dan menyimpan data produk baru ke Cache
     */
    Route::post('/products', function (Request $request) use ($getProducts) {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $products = $getProducts();
        $nextId = empty($products) ? 1 : (max(array_column($products, 'id')) + 1);

        $newProduct = [
            'id' => $nextId,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'price' => (float) $validated['price'],
            'stock' => (int) $validated['stock'],
        ];

        $products[] = $newProduct;
        Cache::forever('umkm_products', $products);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk UMKM berhasil dibuat',
            'data' => $newProduct,
        ], 201);
    });

    /**
     * 3. GET /api/v1/products/{id}
     * Mencari detail produk berdasarkan ID
     */
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
