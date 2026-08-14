<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GalleryCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GalleryCategory $category): void {
            if (blank($category->slug) && filled($category->name)) {
                $category->slug = static::uniqueSlugFrom($category->name, $category->id);
            }
        });
    }

    public static function uniqueSlugFrom(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'gallery-category';
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

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
