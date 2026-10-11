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
        Cache::forget('umkm_categories');
        Cache::forget('umkm_orders');
    }

    public function test_endpoint_1_get_products(): void
    {
        $this->getJson('/api/products')->assertStatus(200)->assertJsonStructure(['data', 'meta']);
    }

    public function test_endpoint_2_get_product_detail(): void
    {
        $this->getJson('/api/products/1')->assertStatus(200)->assertJsonPath('data.id', 1);
    }

    public function test_endpoint_3_post_product(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer admin-token')
            ->postJson('/api/products', [
                'name' => 'Keripik Singkong Balado',
                'price' => 15000,
                'category_id' => 1,
            ]);
        $response->assertStatus(201)->assertHeader('Location', '/api/products/6');
    }

    public function test_endpoint_4_put_product(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer admin-token')
            ->putJson('/api/products/1', [
                'name' => 'Keripik Tempe Super',
                'price' => 18000,
                'category_id' => 1,
            ]);
        $response->assertStatus(200)->assertJsonPath('data.name', 'Keripik Tempe Super');
    }

    public function test_endpoint_5_delete_product(): void
    {
        $this->withHeader('Authorization', 'Bearer admin-token')
            ->deleteJson('/api/products/1')
            ->assertStatus(200);
    }

    public function test_endpoint_6_get_categories(): void
    {
        $this->getJson('/api/categories')->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_endpoint_7_post_orders(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer admin-token')
            ->postJson('/api/orders', [
                'customer_name' => 'Agil Syeh',
                'items' => [['product_id' => 1, 'quantity' => 1]],
            ]);
        $response->assertStatus(201);
    }

    public function test_endpoint_8_get_orders(): void
    {
        $this->withHeader('Authorization', 'Bearer admin-token')
            ->getJson('/api/orders')
            ->assertStatus(200);
    }

    public function test_endpoint_9_get_order_detail(): void
    {
        $this->withHeader('Authorization', 'Bearer admin-token')
            ->getJson('/api/orders/1')
            ->assertStatus(200);
    }

    public function test_endpoint_10_patch_order_status(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer admin-token')
            ->patchJson('/api/orders/1/status', [
                'status' => 'processing',
            ]);
        $response->assertStatus(200)->assertJsonPath('data.status', 'processing');
    }
}