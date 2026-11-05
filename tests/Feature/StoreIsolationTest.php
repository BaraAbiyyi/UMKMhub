<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreIsolationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Store $ownStore;

    private Store $otherStore;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
        $this->admin = User::factory()->umkmAdmin()->create();
        $this->ownStore = Store::factory()->approved()->create([
            'owner_id' => $this->admin->id,
            'category_id' => $this->category->id,
        ]);
        $this->otherStore = Store::factory()->approved()->create(['category_id' => $this->category->id]);
    }

    public function test_admin_cannot_access_another_store(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/my-stores/{$this->otherStore->id}/products")
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN_STORE_ACCESS');
    }

    public function test_admin_can_create_and_update_own_product(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/my-stores/{$this->ownStore->id}/products", [
                'category_id' => $this->category->id,
                'name' => 'Produk Baru',
                'price' => 20000,
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJsonPath('data.name', 'Produk Baru');

        $productId = $response->json('data.id');

        $this->assertDatabaseHas('products', ['id' => $productId, 'store_id' => $this->ownStore->id]);
        $this->assertDatabaseHas('stocks', ['product_id' => $productId, 'quantity' => 0, 'version' => 1]);

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/my-stores/{$this->ownStore->id}/products/{$productId}", ['price' => 25000])
            ->assertOk()
            ->assertJsonPath('data.price', 25000);
    }

    public function test_admin_cannot_create_product_for_another_store(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/my-stores/{$this->otherStore->id}/products", [
                'category_id' => $this->category->id,
                'name' => 'Nyelundup',
                'price' => 1000,
            ])
            ->assertStatus(403);
    }

    public function test_customer_cannot_access_store_management(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer, 'sanctum')
            ->getJson("/api/v1/my-stores/{$this->ownStore->id}/products")
            ->assertStatus(403);
    }

    public function test_suspended_store_products_are_hidden_from_catalog(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->otherStore->id,
            'category_id' => $this->category->id,
        ]);
        Stock::factory()->create(['product_id' => $product->id]);

        $this->otherStore->update(['status' => 'suspended']);

        $this->getJson('/api/v1/products')->assertOk()->assertJsonPath('meta.total', 0);
    }
}
