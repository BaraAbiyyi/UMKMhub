<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('umkm_products');
    }

    /**
     * Test GET /api/v1/products mengembalikan status 200 dan daftar produk.
     */
    public function test_can_get_all_products(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'category', 'price', 'stock'],
                ],
            ])
            ->assertJson([
                'status' => 'success',
            ]);
    }

    /**
     * Test POST /api/v1/products mengembalikan status 201 dan data produk tersimpan (persisten).
     */
    public function test_can_create_new_product_and_persist_in_list(): void
    {
        $payload = [
            'name' => 'Madu Hutan Asli Sumbawa',
            'category' => 'Kesehatan',
            'price' => 85000,
            'stock' => 15,
        ];

        $postResponse = $this->postJson('/api/v1/products', $payload);

        $postResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Produk UMKM berhasil dibuat',
                'data' => [
                    'id' => 6,
                    'name' => 'Madu Hutan Asli Sumbawa',
                    'category' => 'Kesehatan',
                    'price' => 85000,
                    'stock' => 15,
                ],
            ]);

        // Verifikasi produk tersimpan saat dipanggil via GET
        $getResponse = $this->getJson('/api/v1/products/6');
        $getResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => 6,
                    'name' => 'Madu Hutan Asli Sumbawa',
                ],
            ]);

        // Verifikasi ada di list semua produk
        $listResponse = $this->getJson('/api/v1/products');
        $listResponse->assertStatus(200)
            ->assertJsonFragment([
                'id' => 6,
                'name' => 'Madu Hutan Asli Sumbawa',
            ]);
    }

    /**
     * Test GET /api/v1/products/{id} mengembalikan status 200 jika ID ditemukan.
     */
    public function test_can_get_product_by_id_when_found(): void
    {
        $response = $this->getJson('/api/v1/products/1');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => 1,
                    'name' => 'Keripik Tempe Renyah',
                ],
            ]);
    }

    /**
     * Test GET /api/v1/products/{id} mengembalikan status 404 jika ID tidak ditemukan.
     */
    public function test_returns_404_when_product_not_found(): void
    {
        $response = $this->getJson('/api/v1/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan',
            ]);
    }
}
