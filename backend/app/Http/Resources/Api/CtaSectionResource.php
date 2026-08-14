<?php

namespace App\Http\Resources\Api;

use App\Models\CtaSection;
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
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'body' => $this->body,
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
