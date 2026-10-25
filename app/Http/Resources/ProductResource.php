<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => (float) $this->price,
            'category'    => [
                'id'   => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
            'is_active'   => (bool) $this->is_active,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}