<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Melihat detail pesanan: pembeli, pemilik toko pesanan, atau superadmin.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isSuperadmin()
            || $order->user_id === $user->id
            || $order->store?->owner_id === $user->id;
    }

    /**
     * Pembatalan pesanan oleh pembeli.
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return $order->user_id === $user->id && $order->canBeCancelledByBuyer();
    }

    /**
     * Ubah status pesanan: hanya pemilik toko pesanan atau superadmin.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isSuperadmin() || $order->store?->owner_id === $user->id;
    }
}
