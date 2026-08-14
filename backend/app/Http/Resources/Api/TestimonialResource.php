<?php

namespace App\Http\Resources\Api;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Testimonial */
class TestimonialResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'name' => $this->name,
            'quote' => $this->quote,
            'body' => $this->body,
            'avatar' => $this->imageUrl($this->avatar),
            'image' => $this->imageUrl($this->image),
            'rating' => $this->rating,
            'video_url' => $this->video_url,
            'featured' => $this->featured,
            'homepage_featured' => $this->homepage_featured,
            'sort_order' => $this->sort_order,
        ];
    }
}
