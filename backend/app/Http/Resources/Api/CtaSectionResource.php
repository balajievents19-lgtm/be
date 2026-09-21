<?php

namespace App\Http\Resources\Api;

use App\Models\CtaSection;
use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CtaSection */
class CtaSectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key?->value ?? $this->key,
            'title' => Brand::rewrite($this->title),
            'subtitle' => Brand::rewrite($this->subtitle),
            'body' => Brand::rewrite($this->body),
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'secondary_button_text' => $this->secondary_button_text,
            'secondary_button_url' => $this->secondary_button_url,
            'background_image' => $this->imageUrl($this->background_image),
            'sort_order' => $this->sort_order,
            'show_on_homepage' => $this->show_on_homepage,
        ];
    }
}
