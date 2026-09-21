<?php

namespace App\Http\Resources\Api;

use App\Models\ServicePackage;
use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ServicePackage */
class ServicePackageResource extends JsonResource
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
            'summary' => Brand::rewrite($this->summary),
            'description' => Brand::rewrite($this->description),
            'price_label' => $this->price_label,
            'price_amount' => $this->price_amount,
            'currency' => $this->currency,
            'features' => $this->features ?? [],
            'is_featured' => $this->is_featured,
            'image' => $this->imageUrl($this->image),
            'service_id' => $this->service_id,
            'service_category_id' => $this->service_category_id,
            'sort_order' => $this->sort_order,
            'seo' => [
                'title' => Brand::rewrite($this->seo_title),
                'description' => Brand::rewrite($this->seo_description),
            ],
        ];
    }
}
