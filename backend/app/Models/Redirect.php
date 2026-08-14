<?php

namespace App\Models;

use App\Enums\RedirectStatusCode;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasPublicationWindow;

    protected $fillable = [
        'from_path',
        'to_url',
        'status_code',
        'status',
        'is_visible',
        'notes',
        'sort_order',
        'publish_at',
        'unpublish_at',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => RedirectStatusCode::class,
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
}
