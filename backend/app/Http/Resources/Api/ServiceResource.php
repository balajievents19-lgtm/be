<?php

namespace App\Http\Resources\Api;

use App\Support\Brand;
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
            'name' => Brand::rewrite($this->name),
            'slug' => $this->slug,
            'short_description' => Brand::rewrite($this->short_description),
            'full_description' => $this->when(
                $request->routeIs('api.services.show'),
                Brand::rewrite($this->full_description)
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
            'related_gallery' => $this->when(
                $request->routeIs('api.services.show'),
                fn () => GalleryItemResource::collection(
                    $this->relationLoaded('galleryItems') ? $this->galleryItems : collect()
                )
            ),
            'icon' => $this->icon,
            'featured' => $this->featured,
            'show_on_homepage' => $this->show_on_homepage,
            'sort_order' => $this->sort_order,
            'seo' => $this->when($request->routeIs('api.services.show'), [
                'title' => Brand::rewrite($this->seo_title),
                'description' => Brand::rewrite($this->seo_description),
                'keywords' => Brand::rewrite($this->seo_keywords),
                'opengraph_image' => $this->imageUrl($this->opengraph_image),
            ]),
        ];
    }
}
