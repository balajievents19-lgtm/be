<?php

namespace App\Http\Resources\Api;

use App\Models\ExternalMedia;
use App\Services\Media\ExternalMediaUrl;
use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ExternalMedia */
class ExternalMediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resolved = ExternalMediaUrl::resolve((string) $this->url, $this->provider);
        $isYoutube = $resolved['provider'] === 'youtube'
            || ExternalMediaUrl::youtubeId((string) $this->url) !== null;

        return [
            'id' => $this->id,
            'title' => Brand::rewrite($this->title),
            'media_type' => $this->media_type,
            'provider' => $resolved['provider'],
            'url' => $isYoutube ? null : ($resolved['open_url'] ?: $this->url),
            'embed_url' => $resolved['embed_url'],
            'mode' => $resolved['mode'],
            'cta_label' => ($isYoutube || $resolved['mode'] !== 'link')
                ? null
                : ($resolved['message'] ?: 'Open link'),
            'thumbnail' => $this->thumbnail_url,
            'description' => Brand::rewrite($this->description),
            'sort_order' => $this->sort_order,
            'homepage_featured' => (bool) $this->homepage_featured,
        ];
    }
}
