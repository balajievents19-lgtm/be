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
}
