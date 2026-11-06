<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => (int) $this->product->price,
                'image_url' => $this->product->image_url,
                'stock_quantity' => (int) ($this->product->stock?->quantity ?? 0),
                'store' => [
                    'id' => $this->product->store?->id,
                    'name' => $this->product->store?->name,
                    'slug' => $this->product->store?->slug,
                ],
            ]),
            'subtotal' => (int) $this->quantity * (int) ($this->product?->price ?? 0),
        ];
    }
}
