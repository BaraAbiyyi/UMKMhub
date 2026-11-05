<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipient' => $this->recipient,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
