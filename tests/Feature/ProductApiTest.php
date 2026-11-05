<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private Store $store;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->superadmin()->create();
        $this->category = Category::factory()->create();
        $this->store = Store::factory()->approved()->create(['category_id' => $this->category->id]);
    }

    public function test_catalog_lists_only_publicly_visible_products_with_pagination(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
        ]);
        Stock::factory()->create(['product_id' => $product->id, 'quantity' => 10]);

        Product::factory()->inactive()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
        ]);

        $suspended = Store::factory()->suspended()->create();
        Product::factory()->create(['store_id' => $suspended->id]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'category', 'store', 'stock', 'is_active'],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonPath('meta.total', 1);
    }

    public function test_catalog_can_be_filtered_searched_and_sorted(): void
    {
        $murah = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Keripik Tempe',
            'price' => 15000,
        ]);
        $mahal = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Kopi Arabika',
            'price' => 55000,
        ]);

        $this->getJson('/api/v1/products?search=Kopi')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $mahal->id);

        $this->getJson('/api/v1/products?sort=price')
            ->assertOk()
            ->assertJsonPath('data.0.id', $murah->id)
            ->assertJsonPath('data.1.id', $mahal->id);
    }

    public function test_rejects_pagination_above_max_limit(): void
    {
        $this->getJson('/api/v1/products?per_page=500')
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['per_page']]);
    }

    public function test_rejects_invalid_sort(): void
    {
        $this->getJson('/api/v1/products?sort=password')
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['sort']]);
    }

    public function test_can_get_product_detail(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
        ]);

        $this->getJson("/api/v1/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.store.slug', $this->store->slug);
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $product = Product::factory()->inactive()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
        ]);

        $this->getJson("/api/v1/products/{$product->id}")->assertStatus(404);
    }

    public function test_returns_404_when_product_missing(): void
    {
        $this->getJson('/api/v1/products/9999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource tidak ditemukan.']);
    }
}
