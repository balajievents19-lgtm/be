<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use App\Services\Media\ExternalMediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalMedia extends Model
{
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $table = 'external_media';

    protected $fillable = [
        'title',
        'media_type',
        'provider',
        'url',
        'thumbnail',
        'description',
        'status',
        'homepage_featured',
        'sort_order',
        'publish_at',
        'unpublish_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'homepage_featured' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->withinPublicationWindow();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('homepage_featured', true);
    }

    /**
     * @return array{valid: bool, provider: string, embed_url: string|null, open_url: string, mode: string, message: string|null}
     */
    public function resolved(): array
    {
        return ExternalMediaUrl::resolve((string) $this->url, $this->provider);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->imageUrl($this->thumbnail);
    }
}
