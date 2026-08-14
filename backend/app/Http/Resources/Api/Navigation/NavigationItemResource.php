<?php

namespace App\Http\Resources\Api\Navigation;

use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NavigationItem */
class NavigationItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'url' => $this->url,
            'target' => $this->target?->value ?? '_self',
            'icon' => $this->icon,
            'image' => $this->image_url,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],
        ];
    }
}
