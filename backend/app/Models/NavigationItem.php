<?php

namespace App\Models;

use App\Enums\NavigationLinkTarget;
use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    use HasPublicationWindow;
    use HasPublicStorageUrl;

    protected $fillable = [
        'label',
        'url',
        'target',
        'icon',
        'image',
        'parent_id',
        'sort_order',
        'status',
        'is_visible',
        'show_on_header',
        'show_on_footer',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'target' => NavigationLinkTarget::class,
            'status' => 'boolean',
            'is_visible' => 'boolean',
            'show_on_header' => 'boolean',
            'show_on_footer' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->image);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', true)->where('is_visible', true);
    }

    public function scopeHeader(Builder $query): Builder
    {
        return $query->where('show_on_header', true);
    }

    public function scopeFooter(Builder $query): Builder
    {
        return $query->where('show_on_footer', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
