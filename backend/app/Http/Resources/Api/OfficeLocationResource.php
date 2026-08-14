<?php

namespace App\Http\Resources\Api;

use App\Models\OfficeLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OfficeLocation */
class OfficeLocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'phone' => $this->phone,
            'email' => $this->email,
            'map_embed' => $this->map_embed,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_primary' => $this->is_primary,
            'sort_order' => $this->sort_order,
        ];
    }
}
