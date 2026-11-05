<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;

class ProductPolicy
{
    /**
     * Siapa saja bisa melihat produk publik.
     */
    public function view(?User $user, Product $product): bool
    {
        if ($product->is_active && $product->store?->isApproved()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->isSuperadmin() || $product->store?->owner_id === $user->id;
    }

    /**
     * Tambah produk: hanya pemilik toko atau superadmin, dan status toko harus approved.
     */
    public function create(User $user, Store $store): bool
    {
        if (! $store->isApproved() && ! $user->isSuperadmin()) {
            return false;
        }

        return $user->isSuperadmin() || $store->owner_id === $user->id;
    }

    /**
     * Ubah produk: hanya pemilik toko atau superadmin.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isSuperadmin() || $product->store?->owner_id === $user->id;
    }

    /**
     * Hapus produk: hanya pemilik toko atau superadmin.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->isSuperadmin() || $product->store?->owner_id === $user->id;
    }
}
