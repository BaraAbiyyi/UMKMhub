<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (int) $this->price,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'store' => $this->whenLoaded('store', fn () => [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
                'city' => $this->store->city,
                'logo_url' => $this->store->logo_path ? Storage::disk('public')->url($this->store->logo_path) : null,
            ]),
            'stock' => $this->whenLoaded('stock', fn () => [
                'id' => $this->stock?->id,
                'quantity' => (int) $this->stock?->quantity,
                'version' => (int) $this->stock?->version,
            ]),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
