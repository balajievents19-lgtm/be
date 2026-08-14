<?php

namespace App\Models\Concerns\Publication;

use Illuminate\Database\Eloquent\Builder;

trait HasPublicationWindow
{
    public function scopeWithinPublicationWindow(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', now());
            })
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('unpublish_at')
                    ->orWhere('unpublish_at', '>', now());
            });
    }
}
