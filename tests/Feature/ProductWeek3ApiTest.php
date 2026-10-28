<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductWeek3ApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    private Product $product;

    private Customer $customer;

    private Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin UMKMHub',
            'email' => 'admin@umkmhub.id',
            'password' => Hash::make('password123'),
        ]);

        $this->category = Category::create([
            'name' => 'Makanan Ringan',
            'description' => 'Aneka keripik dan camilan',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Keripik Tempe Renyah',
            'description' => 'Keripik tempe khas Malang 200g',
            'price' => 15000,
            'is_active' => true,
        ]);

        $this->stock = Stock::create([
            'product_id' => $this->product->id,
            'quantity' => 50,
            'version' => 1,
        ]);

        $this->customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Semanggi Timur No. 12, Malang',
        ]);
    }

    /**
     * 1. Test POST /api/v1/auth/login mengembalikan format AuthToken yang valid.
     */
    public function test_auth_login_returns_valid_token_structure(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@umkmhub.id',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token_type',
                    'access_token',
                    'expires_in',
                ],
            ])
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.expires_in', 86400);
    }

    /**
     * 2. Test POST /api/v1/auth/login menolak kredensial salah dengan status 401.
     */
    public function test_auth_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@umkmhub.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Email atau password yang Anda masukkan salah.',
            ]);
    }

    /**
     * 3. Test GET & POST /api/v1/categories.
     */
    public function test_category_crud_operations(): void
    {
        $getResponse = $this->getJson('/api/v1/categories');
        $getResponse->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'description']]]);

        $postResponse = $this->postJson('/api/v1/categories', [
            'name' => 'Minuman',
            'description' => 'Kopi dan teh lokal',
        ]);
        $postResponse->assertStatus(201)
            ->assertHeader('Location')
            ->assertJsonPath('data.name', 'Minuman');
    }

    /**
     * 4. Test GET & POST /api/v1/customers.
     */
    public function test_customer_registration(): void
    {
        $response = $this->postJson('/api/v1/customers', [
            'name' => 'Siti Aminah',
            'phone' => '089876543210',
            'address' => 'Jl. Ijen No. 5, Malang',
        ]);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJsonPath('data.name', 'Siti Aminah');

        $this->assertDatabaseHas('customers', [
            'name' => 'Siti Aminah',
            'phone' => '089876543210',
        ]);
    }

    /**
     * 5. Test GET /api/v1/stocks monitoring kuantitas stok.
     */
    public function test_stocks_monitoring_list(): void
    {
        $response = $this->getJson('/api/v1/stocks?low_stock=false&per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'product_id', 'quantity', 'version', 'updated_at'],
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * 6. Test PATCH /api/v1/stocks/{id} Optimistic Concurrency Control (OCC).
     */
    public function test_stock_update_with_optimistic_locking_and_conflict(): void
    {
        // Update sukses jika version cocok
        $successResponse = $this->patchJson("/api/v1/stocks/{$this->stock->id}", [
            'quantity' => 60,
            'version' => 1,
        ]);

        $successResponse->assertStatus(200)
            ->assertJsonPath('data.quantity', 60)
            ->assertJsonPath('data.version', 2);

        // Update gagal (409 Conflict) jika version usang/mismatch
        $conflictResponse = $this->patchJson("/api/v1/stocks/{$this->stock->id}", [
            'quantity' => 70,
            'version' => 1, // versi sekarang sudah 2
        ]);

        $conflictResponse->assertStatus(409)
            ->assertJson([
                'code' => 'STOK_VERSION_MISMATCH',
                'message' => 'Stok telah diperbarui oleh pengguna lain. Silakan muat ulang data terbaru.',
            ]);
    }

    /**
     * 7. Test POST /api/v1/orders checkout pesanan sukses dan memotong stok secara atomik.
     */
    public function test_order_checkout_successful_and_decrements_stock(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_id' => $this->customer->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJsonPath('data.customer_id', $this->customer->id)
            ->assertJsonPath('data.total_amount', 75000)
            ->assertJsonPath('data.status', 'pending');

        // Verifikasi stok berkurang menjadi 45 dan versi bertambah menjadi 2
        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'quantity' => 45,
            'version' => 2,
        ]);
    }

    /**
     * 8. Test POST /api/v1/orders menolak checkout jika stok tidak cukup (409 Conflict).
     */
    public function test_order_checkout_fails_when_stock_insufficient(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_id' => $this->customer->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 100], // stok hanya 50
            ],
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'code' => 'INSUFFICIENT_STOCK',
                'message' => 'Stok tidak mencukupi untuk produk: Keripik Tempe Renyah',
            ]);

        // Verifikasi rollback: stok tetap 50
        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);
    }

    /**
     * 9. Test GET /api/v1/customers/{id}/orders mengembalikan riwayat berpaginasi.
     */
    public function test_customer_orders_returns_paginated_list(): void
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'total_amount' => 30000,
            'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 15000,
            'subtotal' => 30000,
        ]);

        $response = $this->getJson("/api/v1/customers/{$this->customer->id}/orders");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'customer_id', 'total_amount', 'status', 'items'],
                ],
                'links',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * 10. Test Global Exception Handler untuk 404 dan 405 (JSON seragam tanpa trace).
     */
    public function test_global_exception_handling_json_responses(): void
    {
        // 404 Route Not Found
        $response404 = $this->getJson('/api/v1/nonexistent-route-test');
        $response404->assertStatus(404)
            ->assertJson(['message' => 'Resource tidak ditemukan.']);
        $this->assertArrayNotHasKey('trace', $response404->json());

        // 405 Method Not Allowed
        $response405 = $this->deleteJson('/api/v1/stocks');
        $response405->assertStatus(405)
            ->assertJson(['message' => 'Method tidak diizinkan.']);
        $this->assertArrayNotHasKey('trace', $response405->json());
    }
}
