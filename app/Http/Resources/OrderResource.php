<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'customer_id'  => $this->customer_id,
            'total_amount' => (float) $this->total_amount,
            'status'       => $this->status,
            'items'        => $this->items->map(fn ($item) => [
                'id'         => $item->id,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => (float) $item->price,
                'subtotal'   => (float) $item->subtotal,
            ]),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}