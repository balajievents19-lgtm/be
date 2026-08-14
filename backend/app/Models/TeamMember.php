<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'role',
        'bio',
        'photo',
        'email',
        'phone',
        'social_linkedin',
        'social_instagram',
        'sort_order',
        'status',
        'is_visible',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'is_visible' => 'boolean',
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

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->imageUrl($this->photo);
    }
}
