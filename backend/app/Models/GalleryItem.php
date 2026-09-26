<?php

namespace App\Models;

use App\Enums\GalleryMediaType;
use App\Enums\GalleryVideoSource;
use App\Models\Concerns\HasAutoFirstSortOrder;
use App\Models\Concerns\HasContentModeration;
use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GalleryItem extends Model
{
    use HasAutoFirstSortOrder;
    use HasContentModeration;
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $fillable = [
        'gallery_category_id',
        'media_type',
        'title',
        'slug',
        'description',
        'image',
        'original_path',
        'original_disk',
        'thumbnail',
        'alt_text',
        'caption',
        'youtube_url',
        'vimeo_url',
        'video_source',
        'video_url',
        'featured',
        'homepage_featured',
        'sort_order',
        'status',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'opengraph_image',
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
            'featured' => 'boolean',
            'homepage_featured' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
            'media_type' => GalleryMediaType::class,
            'video_source' => GalleryVideoSource::class,
            'brand_review_required' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GalleryItem $item): void {
            if (blank($item->slug) && filled($item->title)) {
                $item->slug = static::uniqueSlugFrom($item->title, $item->id);
            }

            if ($item->media_type === null) {
                $item->media_type = GalleryMediaType::Image;
            }

            if ($item->isVideo() && ! filled($item->image)) {
                $item->image = '';
            }

            if ($item->isVideo() && $item->video_source === GalleryVideoSource::Youtube && filled($item->video_url) && strlen((string) $item->video_url) <= 255) {
                $item->youtube_url = $item->video_url;
            }
        });
    }

    public static function uniqueSlugFrom(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'gallery-item';
        $slug = $base;
        $counter = 1;

        while (
            static::query()
                ->withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function isVideo(): bool
    {
        return $this->media_type === GalleryMediaType::Video;
    }

    public function isImage(): bool
    {
        return ! $this->isVideo();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'gallery_item_service')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->where($table.'.status', true)->withinPublicationWindow()->publiclyModerated();
    }

    /**
     * Public listings must have a thumbnail or a non-private, non-studio image path.
     */
    public function scopeWithPublicPreview(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $outer) use ($table): void {
            $outer->where(function (Builder $q) use ($table): void {
                $q->whereNotNull($table.'.thumbnail')->where($table.'.thumbnail', '!=', '');
            })->orWhere(function (Builder $q) use ($table): void {
                $q->whereNotNull($table.'.image')
                    ->where($table.'.image', '!=', '')
                    ->where($table.'.image', 'not like', '%studio/uploads/%')
                    ->where($table.'.image', 'not like', 'gallery/images/%');
            })->orWhere(function (Builder $q) use ($table): void {
                $q->where($table.'.media_type', GalleryMediaType::Video->value)
                    ->whereNotNull($table.'.video_url')
                    ->where($table.'.video_url', '!=', '');
            });
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->orderBy($table.'.sort_order')->orderBy($table.'.id');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('homepage_featured', true);
    }

    /** Public card/lightbox path — thumbnail first, never studio leftovers. */
    public function publicCoverPath(): ?string
    {
        foreach ([$this->thumbnail, $this->image] as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }
            if (str_contains($path, 'studio/uploads/')) {
                continue;
            }

            return $path;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function autoFirstSortScopeColumns(): array
    {
        return ['gallery_category_id'];
    }
}
