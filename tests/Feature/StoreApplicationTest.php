<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_register_a_store_and_admin_account(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/store-applications', [
            'name' => 'Sari Rahayu',
            'email' => 'sari@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'store_name' => 'Keripik Bu Sari',
            'description' => 'Keripik tempe khas Malang',
            'category_id' => $category->id,
            'city' => 'Malang',
            'address' => 'Jl. Semanggi Timur 12',
            'phone' => '081234567890',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Keripik Bu Sari')
            ->assertJsonPath('data.status', StoreStatus::Pending->value)
            ->assertJsonPath('data.slug', 'keripik-bu-sari');

        $this->assertDatabaseHas('users', ['email' => 'sari@example.com', 'role' => 'umkm_admin']);
        $this->assertDatabaseHas('stores', ['name' => 'Keripik Bu Sari', 'status' => 'pending']);
    }

    public function test_public_store_list_only_shows_approved_stores(): void
    {
        Store::factory()->approved()->create(['name' => 'Toko Aktif']);
        Store::factory()->pending()->create(['name' => 'Toko Pending']);
        Store::factory()->suspended()->create(['name' => 'Toko Suspend']);

        $this->getJson('/api/v1/stores')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Toko Aktif');
    }

    public function test_pending_store_detail_is_not_publicly_visible(): void
    {
        $store = Store::factory()->pending()->create();

        $this->getJson("/api/v1/stores/{$store->slug}")->assertStatus(404);
    }

    public function test_superadmin_can_approve_and_reject_stores(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $pending = Store::factory()->pending()->create();
        $other = Store::factory()->pending()->create();

        $this->actingAs($superadmin, 'sanctum')
            ->postJson("/api/v1/admin/stores/{$pending->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->actingAs($superadmin, 'sanctum')
            ->postJson("/api/v1/admin/stores/{$other->id}/reject", [])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['rejection_reason']]);

        $this->actingAs($superadmin, 'sanctum')
            ->postJson("/api/v1/admin/stores/{$other->id}/reject", ['rejection_reason' => 'Berkas tidak lengkap.'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'Berkas tidak lengkap.');
    }

    public function test_only_superadmin_can_access_admin_endpoints(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/admin/stats')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN_ROLE_ACCESS');
    }
}
