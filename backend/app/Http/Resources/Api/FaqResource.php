<?php

namespace App\Http\Resources\Api;

use App\Models\Faq;
use App\Support\Brand;
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
            'question' => Brand::rewrite($this->question),
            'slug' => $this->slug,
            'answer' => Brand::rewrite($this->answer),
            'featured' => $this->featured,
            'homepage_featured' => $this->homepage_featured,
            'sort_order' => $this->sort_order,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => Brand::rewrite($this->category->name),
                'slug' => $this->category->slug,
            ]),
            'seo' => $this->when($request->routeIs('api.faqs.show'), [
                'title' => Brand::rewrite($this->seo_title),
                'description' => Brand::rewrite($this->seo_description),
            ]),
        ];
    }
}
