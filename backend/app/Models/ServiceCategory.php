<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'sort_order',
        'status',
        'is_visible',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
        'opengraph_image',
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

    protected static function booted(): void
    {
        static::saving(function (ServiceCategory $category): void {
            if (blank($category->slug) && filled($category->name)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class);
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
}
