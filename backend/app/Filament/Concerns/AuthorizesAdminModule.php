<?php

namespace App\Filament\Concerns;

use App\Support\Rbac\AdminModules;
use Illuminate\Support\Facades\Auth;

trait AuthorizesAdminModule
{
    /**
     * Consuming classes must define: protected static string $adminModule = '...';
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        /** @phpstan-ignore-next-line */
        return $user->can(AdminModules::permission(static::$adminModule, 'view'));
    }

    protected static function canUpdateModule(): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        /** @phpstan-ignore-next-line */
        return $user->can(AdminModules::permission(static::$adminModule, 'update'));
    }
}
