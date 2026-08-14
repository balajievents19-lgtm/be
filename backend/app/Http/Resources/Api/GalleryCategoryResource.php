<?php

namespace App\Http\Resources\Api;

use App\Models\GalleryCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GalleryCategory */
class GalleryCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cover = $this->relationLoaded('items')
            ? $this->items->first()
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'image_count' => $this->when(isset($this->items_count), (int) $this->items_count),
            'cover_image' => $cover
                ? ($cover->imageUrl($cover->thumbnail ?: $cover->image))
                : null,
        ];
    }
}
