<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Makanan Ringan',
            'description' => 'Camilan tradisional',
        ]);
    }

    /**
     * Test GET /api/v1/products mengembalikan status 200 dan daftar produk berpaginasi.
     */
    public function test_can_get_all_products_with_pagination(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Keripik Tempe Renyah',
            'description' => 'Keripik tempe khas Malang',
            'price' => 15000,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'category' => ['id', 'name'],
                        'is_active',
                        'created_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * Test GET /api/v1/products?per_page=500 ditolak dengan status 422.
     */
    public function test_rejects_pagination_exceeding_max_limit(): void
    {
        $response = $this->getJson('/api/v1/products?per_page=500');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['per_page'],
            ]);
    }

    /**
     * Test GET /api/v1/products?sort=invalid ditolak dengan status 422.
     */
    public function test_rejects_invalid_sort_parameter(): void
    {
        $response = $this->getJson('/api/v1/products?sort=password');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['sort'],
            ]);
    }

    /**
     * Test POST /api/v1/products membuat produk baru dengan status 201 dan header Location.
     */
    public function test_can_create_new_product(): void
    {
        $payload = [
            'category_id' => $this->category->id,
            'name' => 'Kopi Robusta Dampit 250g',
            'description' => 'Kopi bubuk premium',
            'price' => 35000,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJsonPath('data.name', 'Kopi Robusta Dampit 250g')
            ->assertJsonPath('data.price', 35000);

        $this->assertDatabaseHas('products', [
            'name' => 'Kopi Robusta Dampit 250g',
            'price' => 35000,
        ]);

        // Verifikasi entitas Stock terinisialisasi secara otomatis
        $productId = $response->json('data.id');
        $this->assertDatabaseHas('stocks', [
            'product_id' => $productId,
            'quantity' => 0,
            'version' => 1,
        ]);
    }

    /**
     * Test GET /api/v1/products/{id} mengembalikan detail spesifik produk.
     */
    public function test_can_get_product_detail(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Sambal Pecel Asli',
            'description' => 'Sambal kacang gurih',
            'price' => 18000,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Sambal Pecel Asli');
    }

    /**
     * Test PATCH /api/v1/products/{id} memperbarui sebagian data produk.
     */
    public function test_can_update_product_partially(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Kue Lapis',
            'price' => 20000,
            'is_active' => true,
        ]);

        $response = $this->patchJson("/api/v1/products/{$product->id}", [
            'price' => 22000,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.price', 22000);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => 22000,
        ]);
    }

    /**
     * Test DELETE /api/v1/products/{id} menghapus produk dengan status 204 No Content.
     */
    public function test_can_delete_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Produk Sementara',
            'price' => 10000,
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test GET /api/v1/products/{id} mengembalikan 404 jika ID tidak ditemukan.
     */
    public function test_returns_404_when_product_not_found(): void
    {
        $response = $this->getJson('/api/v1/products/9999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Resource tidak ditemukan.',
            ]);
    }
}
