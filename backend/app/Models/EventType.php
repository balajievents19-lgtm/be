<?php

namespace App\Models;

use App\Models\Concerns\HasAutoFirstSortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventType extends Model
{
    use HasAutoFirstSortOrder;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'status',
        'sort_order',
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
        static::saving(function (EventType $eventType): void {
            if (blank($eventType->slug) && filled($eventType->name)) {
                $eventType->slug = static::uniqueSlugFrom($eventType->name, $eventType->id);
            }
        });
    }

    public static function uniqueSlugFrom(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'event-type';
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
