<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

trait HasAutoFirstSortOrder
{
    public static function bootHasAutoFirstSortOrder(): void
    {
        static::creating(function (self $model): void {
            $model->assignAutoFirstSortOrder();
        });
    }

    /**
     * Sibling columns that share a sort sequence (e.g. category or parent).
     *
     * @return list<string>
     */
    protected function autoFirstSortScopeColumns(): array
    {
        return [];
    }

    protected function assignAutoFirstSortOrder(): void
    {
        $attributes = $this->getAttributes();
        $provided = array_key_exists('sort_order', $attributes) ? $attributes['sort_order'] : null;

        if ($provided !== null && (int) $provided !== 0) {
            return;
        }

        DB::transaction(function (): void {
            $query = static::query();

            if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
                $query->withTrashed();
            }

            foreach ($this->autoFirstSortScopeColumns() as $column) {
                $query->where($column, $this->getAttribute($column));
            }

            $query->lockForUpdate()->increment('sort_order');
            $this->setAttribute('sort_order', 0);
        });
    }
}
