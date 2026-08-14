<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\Rbac\AdminModules;

trait ChecksModulePermission
{
    /**
     * Consuming policies must define: protected static string $module = '...';
     */
    protected function allows(User $user, string $action): bool
    {
        /** @phpstan-ignore-next-line */
        return $user->can(AdminModules::permission(static::$module, $action));
    }
}
