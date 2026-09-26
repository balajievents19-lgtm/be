<?php

namespace App\Models;

use App\Models\Concerns\HasAutoFirstSortOrder;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeLocation extends Model
{
    use HasAutoFirstSortOrder;
    use HasPublicationWindow;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'pincode',
        'phone',
        'email',
        'map_embed',
        'latitude',
        'longitude',
        'is_primary',
        'sort_order',
        'status',
        'is_visible',
        'publish_at',
        'unpublish_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
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
        return $query->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }
}
