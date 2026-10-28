<?php

namespace App\Services;

use App\Exceptions\StockConflictException;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function checkout(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = 0;
            $itemsToCreate = [];

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $stock = Stock::where('product_id', $product->id)->lockForUpdate()->first();

                if (! $stock || $stock->quantity < $item['quantity']) {
                    throw new StockConflictException("Stok tidak mencukupi untuk produk: {$product->name}", 'INSUFFICIENT_STOCK');
                }

                $stock->decrement('quantity', $item['quantity']);
                $stock->increment('version');

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            return $order->load(['customer', 'items.product']);
        });
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        return $order->load(['customer', 'items.product']);
    }
}
