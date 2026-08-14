<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServicePackage extends Model
{
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'service_category_id',
        'name',
        'slug',
        'summary',
        'description',
        'price_label',
        'price_amount',
        'currency',
        'features',
        'is_featured',
        'sort_order',
        'status',
        'is_visible',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_amount' => 'decimal:2',
            'is_featured' => 'boolean',
            'status' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ServicePackage $package): void {
            if (blank($package->slug) && filled($package->name)) {
                $package->slug = Str::slug($package->name);
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
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
