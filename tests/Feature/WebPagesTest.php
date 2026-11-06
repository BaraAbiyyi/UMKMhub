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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPagesTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
    }

    private function approvedStoreWithProduct(): array
    {
        $store = Store::factory()->approved()->create(['category_id' => $this->category->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $this->category->id,
        ]);
        Stock::factory()->create(['product_id' => $product->id, 'quantity' => 10]);

        return [$store, $product];
    }

    public function test_public_pages_render(): void
    {
        [$store, $product] = $this->approvedStoreWithProduct();

        $this->get('/')->assertOk()->assertSee('UMKMHub');
        $this->get('/produk')->assertOk()->assertSee($product->name);
        $this->get('/produk/'.$product->id)->assertOk()->assertSee($product->name);
        $this->get('/umkm')->assertOk()->assertSee($store->name);
        $this->get('/umkm/'.$store->slug)->assertOk()->assertSee($store->name);
        $this->get('/masuk')->assertOk();
        $this->get('/daftar')->assertOk();
        $this->get('/daftar-umkm')->assertOk()->assertSee('Daftarkan UMKM');
    }

    public function test_inactive_product_returns_404(): void
    {
        [$store, $product] = $this->approvedStoreWithProduct();
        $product->update(['is_active' => false]);

        $this->get('/produk/'.$product->id)->assertNotFound();
    }

    public function test_customer_pages_render_with_cart_and_order(): void
    {
        $customer = User::factory()->customer()->create();
        [$store, $product] = $this->approvedStoreWithProduct();
        $address = Address::factory()->default()->create(['user_id' => $customer->id]);

        $cart = Cart::create(['user_id' => $customer->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 2]);

        $order = Order::factory()->paid()->create([
            'user_id' => $customer->id,
            'store_id' => $store->id,
        ]);

        $this->actingAs($customer)->get('/keranjang')->assertOk()->assertSee($product->name);
        $this->actingAs($customer)->get('/checkout')->assertOk()->assertSee($address->recipient);
        $this->actingAs($customer)->get('/pesanan')->assertOk()->assertSee($order->code);
        $this->actingAs($customer)->get('/pesanan/'.$order->id)->assertOk()->assertSee($order->code);
        $this->actingAs($customer)->get('/akun')->assertOk()->assertSee($address->recipient);
    }

    public function test_umkm_dashboard_pages_render(): void
    {
        $admin = User::factory()->umkmAdmin()->create();
        [$store, $product] = $this->approvedStoreWithProduct();
        $store->update(['owner_id' => $admin->id]);

        $order = Order::factory()->paid()->create(['store_id' => $store->id]);

        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee($store->name);
        $this->actingAs($admin)->get('/dashboard/produk')->assertOk()->assertSee($product->name);
        $this->actingAs($admin)->get('/dashboard/stok')->assertOk()->assertSee($product->name);
        $this->actingAs($admin)->get('/dashboard/pesanan')->assertOk()->assertSee($order->code);
        $this->actingAs($admin)->get('/dashboard/pesanan/'.$order->id)->assertOk()->assertSee($order->code);
        $this->actingAs($admin)->get('/dashboard/toko')->assertOk()->assertSee($store->name);
    }

    public function test_pending_umkm_lands_on_status_page(): void
    {
        $admin = User::factory()->umkmAdmin()->create();
        $store = Store::factory()->pending()->create(['owner_id' => $admin->id]);

        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('Status pendaftaran toko');
        $this->actingAs($admin)->get('/dashboard/produk')->assertStatus(403);
        $this->actingAs($admin)->get('/dashboard/toko')->assertOk()->assertSee($store->name);
    }

    public function test_superadmin_pages_render(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        [$store] = $this->approvedStoreWithProduct();
        $pending = Store::factory()->pending()->create(['category_id' => $this->category->id]);
        $order = Order::factory()->paid()->create(['store_id' => $store->id]);

        $this->actingAs($superadmin)->get('/admin')->assertOk()->assertSee('Ringkasan platform');
        $this->actingAs($superadmin)->get('/admin/persetujuan')->assertOk()->assertSee($pending->name);
        $this->actingAs($superadmin)->get('/admin/toko')->assertOk()->assertSee($store->name);
        $this->actingAs($superadmin)->get('/admin/pengguna')->assertOk();
        $this->actingAs($superadmin)->get('/admin/kategori')->assertOk()->assertSee($this->category->name);
        $this->actingAs($superadmin)->get('/admin/pesanan')->assertOk()->assertSee($order->code);
    }

    public function test_web_login_and_checkout_flow(): void
    {
        $customer = User::factory()->customer()->create(['password' => bcrypt('password123')]);
        [$store, $product] = $this->approvedStoreWithProduct();
        $address = Address::factory()->default()->create(['user_id' => $customer->id]);

        $cart = Cart::create(['user_id' => $customer->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->post('/masuk', ['email' => $customer->email, 'password' => 'password123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($customer);

        $response = $this->post('/checkout', ['address_id' => $address->id]);
        $order = Order::firstOrFail();

        $response->assertRedirect(route('checkout.payment', $order));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::PendingPayment->value]);
        $this->assertDatabaseHas('stocks', ['product_id' => $product->id, 'quantity' => 9]);

        $this->get('/pesanan/'.$order->id.'/bayar')->assertOk()->assertSee('MODE SIMULASI');
        $this->post('/pesanan/'.$order->id.'/bayar', ['result' => 'success'])
            ->assertRedirect(route('checkout.done', $order));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Paid->value]);
    }

    public function test_guest_is_redirected_from_customer_pages(): void
    {
        $this->get('/keranjang')->assertRedirect(route('login'));
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }
}
