<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Address $address;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
        $this->customer = User::factory()->customer()->create();
        $this->address = Address::factory()->default()->create(['user_id' => $this->customer->id]);
    }

    private function productInNewStore(int $quantity = 10): Product
    {
        $store = Store::factory()->approved()->create(['category_id' => $this->category->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $this->category->id,
            'price' => 15000,
        ]);
        Stock::factory()->create(['product_id' => $product->id, 'quantity' => $quantity]);

        return $product;
    }

    private function addToCart(Product $product, int $quantity): void
    {
        $cart = Cart::firstOrCreate(['user_id' => $this->customer->id]);
        CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $product->id],
            ['quantity' => $quantity],
        );
    }

    public function test_checkout_splits_cart_into_one_order_per_store_and_cuts_stock(): void
    {
        $first = $this->productInNewStore();
        $second = $this->productInNewStore();

        $this->addToCart($first, 2);
        $this->addToCart($second, 1);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders/checkout', ['address_id' => $this->address->id]);

        $response->assertStatus(201)->assertJsonCount(2, 'data');

        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseHas('orders', ['status' => OrderStatus::PendingPayment->value]);
        $this->assertDatabaseHas('stocks', ['product_id' => $first->id, 'quantity' => 8]);
        $this->assertDatabaseHas('stocks', ['product_id' => $second->id, 'quantity' => 9]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_rolls_back_when_stock_insufficient(): void
    {
        $product = $this->productInNewStore(quantity: 1);
        $this->addToCart($product, 5);

        $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders/checkout', ['address_id' => $this->address->id])
            ->assertStatus(409)
            ->assertJsonPath('code', 'INSUFFICIENT_STOCK');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('stocks', ['product_id' => $product->id, 'quantity' => 1]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_successful_simulated_payment_marks_order_paid(): void
    {
        $product = $this->productInNewStore();
        $this->addToCart($product, 1);

        $orderId = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders/checkout', ['address_id' => $this->address->id])
            ->json('data.0.id');

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/v1/orders/{$orderId}/simulate-payment", ['result' => 'success'])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Paid->value);
    }

    public function test_failed_payment_cancels_order_and_restores_stock(): void
    {
        $product = $this->productInNewStore(quantity: 10);
        $this->addToCart($product, 3);

        $orderId = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders/checkout', ['address_id' => $this->address->id])
            ->json('data.0.id');

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/v1/orders/{$orderId}/simulate-payment", ['result' => 'failed'])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Cancelled->value);

        $this->assertDatabaseHas('stocks', ['product_id' => $product->id, 'quantity' => 10]);
    }

    public function test_buyer_can_cancel_paid_order_and_stock_is_restored(): void
    {
        $product = $this->productInNewStore();
        $this->addToCart($product, 4);

        $orderId = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders/checkout', ['address_id' => $this->address->id])
            ->json('data.0.id');

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/v1/orders/{$orderId}/simulate-payment", ['result' => 'success'])
            ->assertOk();

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/v1/orders/{$orderId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Cancelled->value);

        $this->assertDatabaseHas('stocks', ['product_id' => $product->id, 'quantity' => 10]);
    }

    public function test_overdue_orders_are_cancelled_and_stock_restored(): void
    {
        $product = $this->productInNewStore(quantity: 10);
        $this->addToCart($product, 2);

        $orderId = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders/checkout', ['address_id' => $this->address->id])
            ->json('data.0.id');

        Order::whereKey($orderId)->update(['expires_at' => now()->subMinute()]);

        $count = app(OrderService::class)->expireOverdueOrders();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('orders', ['id' => $orderId, 'status' => OrderStatus::Cancelled->value]);
        $this->assertDatabaseHas('stocks', ['product_id' => $product->id, 'quantity' => 10]);
    }

    public function test_buyer_cannot_view_another_buyers_order(): void
    {
        $other = User::factory()->customer()->create();
        $order = Order::factory()->paid()->create(['user_id' => $other->id]);

        $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertStatus(403);
    }

    public function test_store_admin_can_progress_order_status_and_invalid_transition_is_rejected(): void
    {
        $store = Store::factory()->approved()->create(['category_id' => $this->category->id]);
        $order = Order::factory()->paid()->create(['store_id' => $store->id]);
        $admin = $store->owner;

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/my-stores/{$store->id}/orders/{$order->id}/status", ['status' => 'processing'])
            ->assertOk()
            ->assertJsonPath('data.status', 'processing');

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/my-stores/{$store->id}/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'INVALID_STATUS_TRANSITION');
    }
}
