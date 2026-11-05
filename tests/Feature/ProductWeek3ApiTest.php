<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductWeek3ApiTest extends TestCase
{
    use RefreshDatabase;

    private User $umkmAdmin;

    private Store $store;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
        $this->umkmAdmin = User::factory()->umkmAdmin()->create(['password' => bcrypt('password123')]);
        $this->store = Store::factory()->approved()->create([
            'owner_id' => $this->umkmAdmin->id,
            'category_id' => $this->category->id,
        ]);
    }

    public function test_login_returns_token_and_role(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => $this->umkmAdmin->email,
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.role', 'umkm_admin')
            ->assertJsonStructure(['data' => ['user', 'token_type', 'access_token', 'expires_in']]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => $this->umkmAdmin->email,
            'password' => 'wrong-password',
        ])->assertStatus(401)->assertJsonPath('code', 'INVALID_CREDENTIALS');
    }

    public function test_only_superadmin_can_manage_categories(): void
    {
        $this->actingAs($this->umkmAdmin, 'sanctum')
            ->postJson('/api/v1/categories', ['name' => 'Minuman'])
            ->assertStatus(403);

        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/v1/categories', ['name' => 'Minuman'])
            ->assertStatus(201)
            ->assertHeader('Location');
    }

    public function test_store_admin_can_list_stocks_of_own_store(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
        ]);
        Stock::factory()->create(['product_id' => $product->id, 'quantity' => 10]);

        $this->actingAs($this->umkmAdmin, 'sanctum')
            ->getJson("/api/v1/my-stores/{$this->store->id}/stocks")
            ->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['id', 'quantity', 'version', 'is_low_stock']]]);
    }

    public function test_stock_update_uses_optimistic_concurrency_control(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
        ]);
        $stock = Stock::factory()->create(['product_id' => $product->id, 'quantity' => 50, 'version' => 1]);

        $this->actingAs($this->umkmAdmin, 'sanctum')
            ->patchJson("/api/v1/my-stores/{$this->store->id}/stocks/{$stock->id}", [
                'quantity' => 60,
                'version' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.quantity', 60)
            ->assertJsonPath('data.version', 2);

        $this->actingAs($this->umkmAdmin, 'sanctum')
            ->patchJson("/api/v1/my-stores/{$this->store->id}/stocks/{$stock->id}", [
                'quantity' => 70,
                'version' => 1,
            ])
            ->assertStatus(409)
            ->assertJsonPath('code', 'STOK_VERSION_MISMATCH');
    }

    public function test_global_exception_handler_returns_json_404_and_405(): void
    {
        $this->getJson('/api/v1/nonexistent-route-test')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource tidak ditemukan.'])
            ->assertJsonMissingPath('trace');

        $this->deleteJson('/api/v1/products')
            ->assertStatus(405)
            ->assertJson(['message' => 'Method tidak diizinkan.']);
    }
}
