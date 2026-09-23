<?php

namespace App\Http\Resources\Api;

use App\Enums\GalleryVideoSource;
use App\Models\GalleryItem;
use App\Support\Brand;
use App\Support\Media\GalleryVideoEmbed;
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
        // Public clients get thumbnail + safe preview only — never original_path / private URLs.
        $previewPath = $this->thumbnail ?: $this->publicPreviewPath();
        $isYoutube = $this->isVideo() && $this->video_source === GalleryVideoSource::Youtube;
        $embed = $this->isVideo()
            ? GalleryVideoEmbed::resolve($this->video_source?->value, $this->video_url)
            : null;

        return [
            'id' => $this->id,
            'title' => Brand::rewrite($this->title),
            'slug' => $this->slug,
            'media_type' => $this->isVideo() ? 'video' : 'image',
            'description' => $this->when(
                $request->routeIs('api.gallery.show'),
                Brand::rewrite($this->description)
            ),
            // Backward-compatible keys: both are safe public previews (not downloadable originals).
            'image' => $this->imageUrl($previewPath) ?: ($embed['poster_url'] ?? null),
            'thumbnail' => $this->imageUrl($this->thumbnail ?: $previewPath) ?: ($embed['poster_url'] ?? null),
            'alt_text' => Brand::rewrite($this->alt_text),
            'caption' => Brand::rewrite($this->caption),
            'youtube_url' => null,
            'vimeo_url' => $this->vimeo_url,
            'video_source' => $this->isVideo() ? $this->video_source?->value : null,
            'video_id' => $this->isVideo() ? ($embed['video_id'] ?? null) : null,
            'video_url' => $this->isVideo() && ! $isYoutube ? ($embed['open_url'] ?? null) : null,
            'embed' => $this->when($this->isVideo(), [
                'embed_url' => $embed['embed_url'] ?? null,
                'open_url' => $isYoutube ? null : ($embed['open_url'] ?? null),
                'mode' => $embed['mode'] ?? 'link',
                'poster_url' => $embed['poster_url'] ?? null,
                'cta_label' => $isYoutube ? null : ($embed['cta_label'] ?? null),
            ]),
            'featured' => $this->featured,
            'homepage_featured' => $this->homepage_featured,
            'sort_order' => $this->sort_order,
            'download_available' => $this->isVideo() ? false : filled($this->original_path),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => Brand::rewrite($this->category->name),
                'slug' => $this->category->slug,
            ]),
            'seo' => $this->when($request->routeIs('api.gallery.show'), [
                'title' => Brand::rewrite($this->seo_title),
                'description' => Brand::rewrite($this->seo_description),
                'opengraph_image' => $this->imageUrl($this->opengraph_image),
            ]),
        ];
    }

    /**
     * Prefer thumbnail; if image still points at a public preview (not gallery/images originals), use it.
     */
    private function publicPreviewPath(): ?string
    {
        $image = $this->image;
        if (! is_string($image) || $image === '') {
            return null;
        }

        // Hardening: never treat gallery/images/* as a public preview once originals are private.
        if (str_starts_with($image, 'gallery/images/')) {
            return $this->thumbnail;
        }

        if (str_contains($image, 'studio/uploads/')) {
            return $this->thumbnail;
        }

        return $image;
    }
}
