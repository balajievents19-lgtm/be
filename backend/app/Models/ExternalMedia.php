<?php

namespace App\Models;

use App\Models\Concerns\HasContentModeration;
use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use App\Services\Media\ExternalMediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalMedia extends Model
{
    use HasContentModeration;
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $table = 'external_media';

    protected $fillable = [
        'gallery_category_id',
        'service_id',
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
        'created_by',
        'updated_by',
        'moderation_status',
        'brand_review_required',
        'moderation_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'homepage_featured' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
            'brand_review_required' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->withinPublicationWindow()
            ->publiclyModerated();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('homepage_featured', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function resolved(): array
    {
        return ExternalMediaUrl::resolve((string) $this->url, $this->provider);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->imageUrl($this->thumbnail);
    }
}
