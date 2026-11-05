<?php

namespace App\Services;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Exceptions\ForbiddenStoreAccessException;
use App\Exceptions\StoreLimitReachedException;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreService
{
    /**
     * Pendaftaran UMKM baru sekaligus membuat akun Admin UMKM.
     */
    public function register(array $data): Store
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::UmkmAdmin,
            ]);

            $logoPath = isset($data['logo']) ? $data['logo']->store('stores/logos', 'public') : null;

            $store = Store::create([
                'owner_id' => $user->id,
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['store_name'],
                'slug' => $this->uniqueSlug($data['store_name']),
                'description' => $data['description'] ?? null,
                'logo_path' => $logoPath,
                'city' => $data['city'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'status' => StoreStatus::Pending,
                'submitted_at' => now(),
            ]);

            return $store->load('category');
        });
    }

    public function updateProfile(Store $store, array $data): Store
    {
        if (array_key_exists('store_name', $data)) {
            $data['name'] = $data['store_name'];
            unset($data['store_name']);
        }

        if (isset($data['logo'])) {
            if ($store->logo_path) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $data['logo_path'] = $data['logo']->store('stores/logos', 'public');
            unset($data['logo']);
        }

        $store->update($data);

        return $store->fresh('category');
    }

    public function updateLogo(Store $store, $file): Store
    {
        if ($store->logo_path) {
            Storage::disk('public')->delete($store->logo_path);
        }

        $store->update(['logo_path' => $file->store('stores/logos', 'public')]);

        return $store->fresh('category');
    }

    /** Memastikan toko milik akun ini, kalau bukan lempar 403. */
    public function authorizeOwner(User $user, Store $store): void
    {
        if (! $user->isSuperadmin() && $store->owner_id !== $user->id) {
            throw new ForbiddenStoreAccessException;
        }
    }

    public function ensureCanCreateAnother(User $user): void
    {
        if ($user->stores()->count() >= (int) config('umkmhub.max_stores_per_owner')) {
            throw new StoreLimitReachedException;
        }
    }

    /**
     * Daftar toko publik (approved) dengan pencarian dan filter kategori.
     */
    public function listPublic(array $filters): LengthAwarePaginator
    {
        return Store::query()
            ->where('status', StoreStatus::Approved)
            ->with('category')
            ->withCount('products')
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 12))
            ->withQueryString();
    }

    public function listApplications(array $filters): LengthAwarePaginator
    {
        return Store::query()
            ->with(['category', 'owner'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhere('city', 'like', "%{$term}%")))
            ->latest('submitted_at')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function approve(Store $store, User $reviewer): Store
    {
        $store->update([
            'status' => StoreStatus::Approved,
            'rejection_reason' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
        ]);

        return $store;
    }

    public function reject(Store $store, User $reviewer, string $reason): Store
    {
        $store->update([
            'status' => StoreStatus::Rejected,
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
        ]);

        return $store;
    }

    public function setStatus(Store $store, StoreStatus $status): Store
    {
        $store->update(['status' => $status]);

        return $store;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'toko';
        $slug = $base;
        $i = 1;

        while (Store::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
