<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class LeadAssignees
{
    /**
     * Constrain a User query to users who can work with Leads (`leads.view`).
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public static function constrain(Builder $query): Builder
    {
        return $query
            ->permission('leads.view')
            ->orderBy('name');
    }

    /**
     * @return Builder<User>
     */
    public static function query(): Builder
    {
        return self::constrain(User::query());
    }

    /**
     * @return array<int|string, string>
     */
    public static function options(): array
    {
        return self::query()
            ->pluck('name', 'id')
            ->all();
    }

    public static function isAssignable(?int $userId): bool
    {
        if ($userId === null) {
            return true;
        }

        return self::query()->whereKey($userId)->exists();
    }
}
