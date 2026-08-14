<?php

namespace App\Http\Resources\Api;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Faq */
class FaqResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'slug' => $this->slug,
            'answer' => $this->answer,
            'featured' => $this->featured,
            'homepage_featured' => $this->homepage_featured,
            'sort_order' => $this->sort_order,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'seo' => $this->when($request->routeIs('api.faqs.show'), [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ]),
        ];
    }
}
