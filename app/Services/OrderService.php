<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\ApiException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\StockConflictException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Checkout atomik: satu pesanan per toko, stok dipotong dengan row-level lock.
     *
     * @return Collection<int, Order>
     */
    public function checkout(User $user, int $addressId): Collection
    {
        return DB::transaction(function () use ($user, $addressId) {
            $cart = Cart::query()->where('user_id', $user->id)->with('items')->first();

            if (! $cart || $cart->items->isEmpty()) {
                throw new ApiException('Keranjang masih kosong.', 'CART_EMPTY', 422);
            }

            $address = $user->addresses()->findOrFail($addressId);
            $addressSnapshot = [
                'recipient' => $address->recipient,
                'phone' => $address->phone,
                'address' => $address->address,
                'city' => $address->city,
            ];

            // Kelompokkan item keranjang per toko.
            $itemsByStore = $cart->items->groupBy(fn ($item) => $item->product->store_id);
            $orders = new Collection;

            foreach ($itemsByStore as $storeId => $items) {
                $subtotal = 0;
                $preparedItems = [];

                foreach ($items as $cartItem) {
                    $product = Product::findOrFail($cartItem->product_id);
                    $stock = Stock::query()
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $stock || $stock->quantity < $cartItem->quantity) {
                        throw new StockConflictException(
                            "Stok tidak mencukupi untuk produk: {$product->name}",
                            'INSUFFICIENT_STOCK',
                        );
                    }

                    $stock->decrement('quantity', $cartItem->quantity);
                    $stock->increment('version');

                    $lineTotal = (int) $product->price * $cartItem->quantity;
                    $subtotal += $lineTotal;
                    $preparedItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => (int) $product->price,
                        'quantity' => $cartItem->quantity,
                    ];
                }

                $shipping = (int) config('umkmhub.shipping_cost');

                $order = Order::create([
                    'user_id' => $user->id,
                    'store_id' => $storeId,
                    'status' => OrderStatus::PendingPayment,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping,
                    'total' => $subtotal + $shipping,
                    'shipping_address' => $addressSnapshot,
                    'expires_at' => now()->addMinutes((int) config('umkmhub.payment_expiry_minutes')),
                ]);

                foreach ($preparedItems as $item) {
                    $order->items()->create($item);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'method' => 'simulation',
                    'status' => PaymentStatus::Pending,
                ]);

                $orders->push($order);
            }

            $cart->items()->delete();

            return $orders->load(['store', 'items', 'payment']);
        });
    }

    /**
     * Simulasi hasil pembayaran.
     */
    public function simulatePayment(Order $order, string $result): Order
    {
        return DB::transaction(function () use ($order, $result) {
            if ($order->status !== OrderStatus::PendingPayment) {
                throw new InvalidStatusTransitionException($order->status, match ($result) {
                    'success' => OrderStatus::Paid,
                    default => OrderStatus::Cancelled,
                });
            }

            $payment = $order->payment ?? Payment::create([
                'order_id' => $order->id,
                'method' => 'simulation',
                'status' => PaymentStatus::Pending,
            ]);

            if ($result === 'success') {
                $order->update(['status' => OrderStatus::Paid, 'expires_at' => null]);
                $payment->update(['status' => PaymentStatus::Success, 'paid_at' => now()]);
            } else {
                $order->update(['status' => OrderStatus::Cancelled, 'expires_at' => null]);
                $payment->update(['status' => $result === 'expired' ? PaymentStatus::Expired : PaymentStatus::Failed]);
                $this->restoreStock($order);
            }

            return $order->fresh(['store', 'items', 'payment']);
        });
    }

    /**
     * Pembatalan pesanan. Stok dikembalikan bila pesanan sudah dibayar.
     */
    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            if (! $order->canBeCancelledByBuyer()) {
                throw new InvalidStatusTransitionException($order->status, OrderStatus::Cancelled);
            }

            $wasPaid = in_array($order->status, [OrderStatus::Paid], strict: true);

            $order->update(['status' => OrderStatus::Cancelled, 'expires_at' => null]);

            if ($order->payment) {
                $order->payment->update([
                    'status' => $wasPaid ? PaymentStatus::RefundedSimulation : PaymentStatus::Failed,
                ]);
            }

            $this->restoreStock($order);

            return $order->fresh(['store', 'items', 'payment']);
        });
    }

    /**
     * Ubah status oleh Admin UMKM sesuai siklus.
     */
    public function updateStatus(Order $order, OrderStatus $next): Order
    {
        if (! $order->status->canTransitionTo($next)) {
            throw new InvalidStatusTransitionException($order->status, $next);
        }

        $order->update(['status' => $next]);

        return $order->fresh(['store', 'items', 'payment']);
    }

    /**
     * Batalkan otomatis pesanan yang melewati batas bayar (dipanggil scheduler).
     */
    public function expireOverdueOrders(): int
    {
        $expired = Order::query()
            ->where('status', OrderStatus::PendingPayment)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $order) {
            DB::transaction(function () use ($order) {
                $order->update(['status' => OrderStatus::Cancelled, 'expires_at' => null]);
                $order->payment?->update(['status' => PaymentStatus::Expired]);
                $this->restoreStock($order);
            });
        }

        return $expired->count();
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $stock = Stock::query()->where('product_id', $item->product_id)->lockForUpdate()->first();
            if ($stock) {
                $stock->increment('quantity', $item->quantity);
                $stock->increment('version');
            }
        }
    }
}
