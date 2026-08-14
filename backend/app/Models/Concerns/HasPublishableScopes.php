<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPublishableScopes
{
    public function scopeActive(Builder $query): Builder
    {
        $query->where('status', true);

        if (method_exists($query->getModel(), 'scopeWithinPublicationWindow')) {
            $query->withinPublicationWindow();
        }

        return $query;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('homepage_featured', true);
    }
}
