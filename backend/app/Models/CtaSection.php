<?php

namespace App\Models;

use App\Enums\CtaSectionKey;
use App\Models\Concerns\HasAutoFirstSortOrder;
use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CtaSection extends Model
{
    use HasAutoFirstSortOrder;
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'body',
        'button_text',
        'button_url',
        'secondary_button_text',
        'secondary_button_url',
        'background_image',
        'sort_order',
        'status',
        'is_visible',
        'show_on_homepage',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'key' => CtaSectionKey::class,
            'status' => 'boolean',
            'is_visible' => 'boolean',
            'show_on_homepage' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->where('is_visible', true)
            ->withinPublicationWindow();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('show_on_homepage', true);
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->background_image);
    }
}
