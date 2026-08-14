<?php

namespace App\Http\Resources\Api;

use App\Models\ServicePackage;
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
            'name' => $this->name,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
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
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],
        ];
    }
}
