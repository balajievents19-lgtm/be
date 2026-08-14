<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Service */
class ServiceResource extends JsonResource
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
            'short_description' => $this->short_description,
            'full_description' => $this->when(
                $request->routeIs('api.services.show'),
                $this->full_description
            ),
            'featured_image' => $this->imageUrl($this->featured_image),
            'banner_image' => $this->when(
                $request->routeIs('api.services.show'),
                $this->imageUrl($this->banner_image)
            ),
            'gallery_images' => $this->when(
                $request->routeIs('api.services.show'),
                $this->galleryImageUrls()
            ),
            'icon' => $this->icon,
            'featured' => $this->featured,
            'show_on_homepage' => $this->show_on_homepage,
            'sort_order' => $this->sort_order,
            'seo' => $this->when($request->routeIs('api.services.show'), [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
                'keywords' => $this->seo_keywords,
                'opengraph_image' => $this->imageUrl($this->opengraph_image),
            ]),
        ];
    }
}
