<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'version' => (int) $this->version,
            'is_low_stock' => $this->isLowStock(),
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'category' => $this->product->category?->name,
            ]),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
