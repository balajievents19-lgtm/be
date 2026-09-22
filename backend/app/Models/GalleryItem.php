<?php

namespace App\Models;

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
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $fillable = [
        'gallery_category_id',
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
        'featured',
        'homepage_featured',
        'sort_order',
        'status',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
        'opengraph_image',
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
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GalleryItem $item): void {
            if (blank($item->slug) && filled($item->title)) {
                $item->slug = static::uniqueSlugFrom($item->title, $item->id);
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

        return $query->where($table.'.status', true)->withinPublicationWindow();
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
}
