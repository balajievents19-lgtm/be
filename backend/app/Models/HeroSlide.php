<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use HasPublicationWindow;
    use HasPublicStorageUrl;

    protected $fillable = [
        'title',
        'title_highlight',
        'subtitle',
        'button_text',
        'button_url',
        'secondary_button_text',
        'secondary_button_url',
        'desktop_image',
        'mobile_image',
        'video_url',
        'overlay_opacity',
        'text_alignment',
        'status',
        'sort_order',
        'publish_at',
        'unpublish_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'overlay_opacity' => 'integer',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true)->withinPublicationWindow();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getDesktopImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->desktop_image);
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->mobile_image);
    }
}
