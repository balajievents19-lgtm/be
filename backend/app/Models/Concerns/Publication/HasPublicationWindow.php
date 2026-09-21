<?php

namespace App\Models\Concerns\Publication;

use Illuminate\Database\Eloquent\Builder;

trait HasPublicationWindow
{
    public function scopeWithinPublicationWindow(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->where(function (Builder $builder) use ($table): void {
                $builder
                    ->whereNull($table.'.publish_at')
                    ->orWhere($table.'.publish_at', '<=', now());
            })
            ->where(function (Builder $builder) use ($table): void {
                $builder
                    ->whereNull($table.'.unpublish_at')
                    ->orWhere($table.'.unpublish_at', '>', now());
            });
    }
}
