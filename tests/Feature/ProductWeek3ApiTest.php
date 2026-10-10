<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductWeek3ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('umkm_products');
    }

    /**
     * Test GET /api/products mengembalikan status 200, format {"data": ...}, dan meta paginasi.
     */
    public function test_can_get_all_products_with_meta_pagination(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'price', 'category_id', 'is_active', 'created_at'],
                ],
                'meta' => ['page', 'per_page', 'total'],
            ])
            ->assertJson([
                'meta' => [
                    'page' => 1,
                    'per_page' => 10,
                    'total' => 5,
                ],
            ]);
    }

    /**
     * Test GET /api/products mendukung query pencarian (?search=).
     */
    public function test_can_search_products_by_keyword(): void
    {
        $response = $this->getJson('/api/products?search=Kopi');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Kopi Robusta Dampit 250g');
    }

    /**
     * Test GET /api/products mendukung filter kategori (?category_id=).
     */
    public function test_can_filter_products_by_category_id(): void
    {
        $response = $this->getJson('/api/products?category_id=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Keripik Tempe dan Sambal Roa
    }

    /**
     * Test POST /api/products mengembalikan 401 Unauthorized jika tanpa Bearer token.
     */
    public function test_post_product_returns_401_when_unauthenticated(): void
    {
        $payload = [
            'name' => 'Keripik Singkong',
            'description' => 'Keripik singkong pedas 250 gram',
            'price' => 15000,
            'category_id' => 2,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Autentikasi diperlukan',
                ],
            ]);
    }

    /**
     * Test POST /api/products mengembalikan 403 Forbidden jika token bukan admin.
     */
    public function test_post_product_returns_403_when_forbidden(): void
    {
        $payload = [
            'name' => 'Keripik Singkong',
            'description' => 'Keripik singkong pedas 250 gram',
            'price' => 15000,
            'category_id' => 2,
        ];

        $response = $this->withHeader('Authorization', 'Bearer customer-token')
            ->postJson('/api/products', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Anda tidak berhak menambah produk',
                ],
            ]);
    }

    /**
     * Test POST /api/products mengembalikan 422 Unprocessable Content jika validasi gagal.
     */
    public function test_post_product_returns_422_when_validation_fails(): void
    {
        $payload = [
            'name' => '',
            'price' => -5000,
            'category_id' => 0,
        ];

        $response = $this->withHeader('Authorization', 'Bearer admin-valid-token')
            ->postJson('/api/products', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'code',
                    'message',
                    'details' => ['name', 'price', 'category_id'],
                ],
            ])
            ->assertJson([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validasi gagal',
                ],
            ]);
    }

    /**
     * Test POST /api/products sukses membuat produk baru (Spesifikasi C1 Bara Abiyyi).
     * Mengembalikan 201 Created, Header Location, dan format {"data": ...}.
     */
    public function test_post_product_success_creates_product_with_location_header(): void
    {
        $payload = [
            'name' => 'Keripik Singkong',
            'description' => 'Keripik singkong pedas 250 gram',
            'price' => 15000,
            'category_id' => 2,
        ];

        $response = $this->withHeader('Authorization', 'Bearer admin-valid-token')
            ->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertHeader('Location', '/api/products/6')
            ->assertJson([
                'data' => [
                    'id' => 6,
                    'name' => 'Keripik Singkong',
                    'description' => 'Keripik singkong pedas 250 gram',
                    'price' => 15000,
                    'category_id' => 2,
                    'is_active' => true,
                ],
            ]);

        // Verifikasi dapat diambil lewat GET /api/products/6
        $getResponse = $this->getJson('/api/products/6');
        $getResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 6,
                    'name' => 'Keripik Singkong',
                ],
            ]);
    }

    /**
     * Test GET /api/products/{id} mengembalikan 404 jika produk tidak ditemukan.
     */
    public function test_get_product_by_id_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Produk tidak ditemukan',
                ],
            ]);
    }
}
