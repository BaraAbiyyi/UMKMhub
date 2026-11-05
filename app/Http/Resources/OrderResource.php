<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'subtotal' => (int) $this->subtotal,
            'shipping_cost' => (int) $this->shipping_cost,
            'total' => (int) $this->total,
            'shipping_address' => $this->shipping_address,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'can_cancel' => $this->canBeCancelledByBuyer(),
            'store' => $this->whenLoaded('store', fn () => [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
            ]),
            'buyer' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => $this->whenLoaded('payment', fn () => $this->payment ? [
                'status' => $this->payment->status->value,
                'paid_at' => $this->payment->paid_at?->toIso8601String(),
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
