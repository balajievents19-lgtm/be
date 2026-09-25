<?php

namespace App\Models;

use App\Models\Concerns\HasContentModeration;
use App\Models\Concerns\HasPublicStorageUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasContentModeration;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'banner_image',
        'thumbnail',
        'alt_text',
        'featured',
        'homepage_featured',
        'published_at',
        'unpublish_at',
        'status',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'opengraph_image',
        'schema_type',
        'reading_time',
        'author',
        'tags',
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
            'published_at' => 'datetime',
            'unpublish_at' => 'datetime',
            'reading_time' => 'integer',
            'tags' => 'array',
            'brand_review_required' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post): void {
            if (blank($post->slug) && filled($post->title)) {
                $post->slug = static::uniqueSlugFrom($post->title, $post->id);
            }

            if (blank($post->reading_time) && filled($post->content)) {
                $post->reading_time = static::estimateReadingTime((string) $post->content);
            }
        });
    }

    public static function uniqueSlugFrom(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'blog-post';
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

    public static function estimateReadingTime(string $html): int
    {
        $text = trim(strip_tags($html));
        $words = str_word_count($text);

        return max(1, (int) ceil($words / 200));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('unpublish_at')
                    ->orWhere('unpublish_at', '>', now());
            })
            ->publiclyModerated();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('homepage_featured', true);
    }
}
