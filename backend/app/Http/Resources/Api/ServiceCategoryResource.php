<?php

namespace App\Http\Resources\Api;

use App\Models\ServiceCategory;
use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ServiceCategory */
class ServiceCategoryResource extends JsonResource
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
            'description' => Brand::rewrite($this->description),
            'image' => $this->imageUrl($this->image),
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'seo' => [
                'title' => Brand::rewrite($this->seo_title),
                'description' => Brand::rewrite($this->seo_description),
                'opengraph_image' => $this->imageUrl($this->opengraph_image),
            ],
        ];
    }
}
