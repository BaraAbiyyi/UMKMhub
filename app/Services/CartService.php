<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

class CartService
{
    public function getCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id])
            ->load(['items.product.store', 'items.product.stock']);
    }

    public function addItem(User $user, int $productId, int $quantity): Cart
    {
        $product = Product::findOrFail($productId);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $existing = $cart->items()->where('product_id', $productId)->first();

        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $quantity]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $this->getCart($user);
    }

    public function updateItem(CartItem $item, int $quantity): Cart
    {
        $item->update(['quantity' => $quantity]);

        return $this->getCart($item->cart->user);
    }

    public function removeItem(CartItem $item): Cart
    {
        $user = $item->cart->user;
        $item->delete();

        return $this->getCart($user);
    }

    /**
     * Ringkasan per toko: subtotal, ongkir simulasi, total.
     *
     * @return array<int, array{store: array, items: mixed, subtotal: int, shipping_cost: int, total: int}>
     */
    public function groupedSummary(Cart $cart): array
    {
        $shipping = (int) config('umkmhub.shipping_cost');

        return $cart->items
            ->groupBy(fn ($item) => $item->product->store_id)
            ->map(function ($items) use ($shipping) {
                $subtotal = $items->sum(fn ($item) => (int) $item->product->price * $item->quantity);

                return [
                    'store' => [
                        'id' => $items->first()->product->store->id,
                        'name' => $items->first()->product->store->name,
                        'slug' => $items->first()->product->store->slug,
                    ],
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping,
                    'total' => $subtotal + $shipping,
                ];
            })
            ->values()
            ->all();
    }
}
