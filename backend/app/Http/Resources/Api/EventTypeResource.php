<?php

namespace App\Http\Resources\Api;

use App\Models\EventType;
use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EventType */
class EventTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => Brand::rewrite($this->name),
            'slug' => $this->slug,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
        ];
    }
}
