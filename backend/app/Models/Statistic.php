<?php

namespace App\Models;

use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statistic extends Model
{
    use HasPublicationWindow;
    use SoftDeletes;

    protected $fillable = [
        'label',
        'value',
        'icon',
        'suffix',
        'sort_order',
        'status',
        'is_visible',
        'show_on_homepage',
        'publish_at',
        'unpublish_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'is_visible' => 'boolean',
            'show_on_homepage' => 'boolean',
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

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('show_on_homepage', true);
    }
}
