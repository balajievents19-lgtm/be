<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $fillable = [
        'service_category_id',
        'name',
        'slug',
        'short_description',
        'full_description',
        'featured_image',
        'banner_image',
        'gallery_images',
        'icon',
        'sort_order',
        'featured',
        'status',
        'show_on_homepage',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'opengraph_image',
    ];

    protected function casts(): array
    {
        return [
            'gallery_images' => 'array',
            'featured' => 'boolean',
            'status' => 'boolean',
            'show_on_homepage' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            if (blank($service->slug) && filled($service->name)) {
                $service->slug = static::uniqueSlugFrom($service->name, $service->id);
            }
        });
    }

    public static function uniqueSlugFrom(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'service';
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
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true)->withinPublicationWindow();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('show_on_homepage', true);
    }

    /**
     * @return list<string>
     */
    public function galleryImageUrls(): array
    {
        return collect($this->gallery_images ?? [])
            ->filter()
            ->map(fn (string $path) => $this->imageUrl($path))
            ->filter()
            ->values()
            ->all();
    }
}
