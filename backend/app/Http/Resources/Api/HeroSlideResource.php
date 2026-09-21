<?php

namespace App\Http\Resources\Api;

use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\HeroSlide */
class HeroSlideResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => Brand::rewrite($this->title),
            'title_highlight' => Brand::rewrite($this->title_highlight),
            'subtitle' => Brand::rewrite($this->subtitle),
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'secondary_button_text' => $this->secondary_button_text,
            'secondary_button_url' => $this->secondary_button_url,
            'desktop_image' => $this->desktop_image_url,
            'mobile_image' => $this->mobile_image_url,
            'video_url' => $this->video_url,
            'overlay_opacity' => $this->overlay_opacity,
            'text_alignment' => $this->text_alignment,
            'sort_order' => $this->sort_order,
        ];
    }
}
