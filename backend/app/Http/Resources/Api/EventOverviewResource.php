<?php

namespace App\Http\Resources\Api;

use App\Models\EventOverview;
use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EventOverview */
class EventOverviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => Brand::rewrite($this->title),
            'caption' => Brand::rewrite($this->caption),
            'description' => Brand::rewrite($this->description),
            'image' => $this->imageUrl($this->image),
            'link_url' => $this->link_url,
            'featured' => $this->featured,
            'homepage_featured' => $this->homepage_featured,
            'sort_order' => $this->sort_order,
        ];
    }
}
