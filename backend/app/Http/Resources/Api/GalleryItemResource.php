<?php

namespace App\Http\Resources\Api;

use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GalleryItem */
class GalleryItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->when(
                $request->routeIs('api.gallery.show'),
                $this->description
            ),
            'image' => $this->imageUrl($this->image),
            'thumbnail' => $this->imageUrl($this->thumbnail),
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'youtube_url' => $this->youtube_url,
            'vimeo_url' => $this->vimeo_url,
            'featured' => $this->featured,
            'homepage_featured' => $this->homepage_featured,
            'sort_order' => $this->sort_order,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'seo' => $this->when($request->routeIs('api.gallery.show'), [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
                'opengraph_image' => $this->imageUrl($this->opengraph_image),
            ]),
        ];
    }
}
