<?php

namespace App\Http\Resources\Api;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BlogPost */
class BlogPostResource extends JsonResource
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
            'excerpt' => $this->excerpt,
            'content' => $this->when(
                $request->routeIs('api.blog.show'),
                $this->content
            ),
            'featured_image' => $this->imageUrl($this->featured_image),
            'banner_image' => $this->when(
                $request->routeIs('api.blog.show'),
                $this->imageUrl($this->banner_image)
            ),
            'thumbnail' => $this->imageUrl($this->thumbnail),
            'alt_text' => $this->alt_text,
            'featured' => $this->featured,
            'homepage_featured' => $this->homepage_featured,
            'published_at' => $this->published_at?->toIso8601String(),
            'reading_time' => $this->reading_time,
            'author' => $this->author,
            'tags' => $this->tags ?? [],
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'seo' => $this->when($request->routeIs('api.blog.show'), [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
                'keywords' => $this->seo_keywords,
                'canonical_url' => $this->canonical_url,
                'opengraph_image' => $this->imageUrl($this->opengraph_image),
                'schema_type' => $this->schema_type,
            ]),
        ];
    }
}
