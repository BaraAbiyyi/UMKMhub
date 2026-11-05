<?php

namespace App\Policies;

use App\Enums\StoreStatus;
use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    /**
     * Semua pengguna bisa melihat daftar toko yang disetujui.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Detail toko: jika approved bisa dilihat publik, jika belum hanya owner atau superadmin.
     */
    public function view(?User $user, Store $store): bool
    {
        if ($store->status === StoreStatus::Approved) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->isSuperadmin() || $store->owner_id === $user->id;
    }

    /**
     * Update profil toko: hanya pemilik toko atau superadmin.
     */
    public function update(User $user, Store $store): bool
    {
        return $user->isSuperadmin() || $store->owner_id === $user->id;
    }

    /**
     * Mengelola produk/stok/pesanan di toko: pemilik toko atau superadmin.
     */
    public function manage(User $user, Store $store): bool
    {
        return $user->isSuperadmin() || $store->owner_id === $user->id;
    }

    /**
     * Persetujuan / Penolakan / Suspend: khusus superadmin.
     */
    public function review(User $user, Store $store): bool
    {
        return $user->isSuperadmin();
    }
}
