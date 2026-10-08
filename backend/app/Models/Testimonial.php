<?php

namespace App\Models;

use App\Enums\TestimonialType;
use App\Models\Concerns\HasAutoFirstSortOrder;
use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\HasPublishableScopes;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Testimonial extends Model
{
    use HasAutoFirstSortOrder;
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use HasPublishableScopes;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'quote',
        'body',
        'avatar',
        'image',
        'rating',
        'video_url',
        'sort_order',
        'featured',
        'homepage_featured',
        'google_reviews_featured',
        'status',
        'publish_at',
        'unpublish_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TestimonialType::class,
            'featured' => 'boolean',
            'homepage_featured' => 'boolean',
            'google_reviews_featured' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer',
            'rating' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }

    public function scopeClientSays(Builder $query): Builder
    {
        return $query->where('type', TestimonialType::ClientSays);
    }

    public function scopeSuccessStories(Builder $query): Builder
    {
        return $query->where('type', TestimonialType::SuccessStory);
    }

    public function scopeGoogleReviewsFeatured(Builder $query): Builder
    {
        return $query
            ->clientSays()
            ->where('google_reviews_featured', true);
    }

    public static function publishedGoogleReviewsFeaturedCount(?int $exceptId = null): int
    {
        return static::query()
            ->active()
            ->googleReviewsFeatured()
            ->when($exceptId, fn (Builder $query) => $query->where('id', '!=', $exceptId))
            ->count();
    }

    protected static function booted(): void
    {
        static::saving(function (Testimonial $testimonial): void {
            if ($testimonial->type !== TestimonialType::ClientSays) {
                $testimonial->google_reviews_featured = false;

                return;
            }

            if (! $testimonial->google_reviews_featured || ! $testimonial->status) {
                return;
            }

            if (static::publishedGoogleReviewsFeaturedCount($testimonial->id) >= 4) {
                throw ValidationException::withMessages([
                    'google_reviews_featured' => 'At most 4 published Client Reviews can be featured in the Google Reviews section.',
                ]);
            }
        });
    }
}
