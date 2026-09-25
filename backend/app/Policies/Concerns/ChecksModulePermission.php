<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Policies\ExternalMediaPolicy;
use App\Policies\GalleryItemPolicy;
use App\Support\Rbac\AdminModules;
use App\Support\Staff\StaffPanelAccess;

trait ChecksModulePermission
{
    /**
     * Consuming policies must define: protected static string $module = '...';
     */
    protected function allows(User $user, string $action): bool
    {
        $restrictedAllowed = static::class === GalleryItemPolicy::class
            || static::class === ExternalMediaPolicy::class;

        if (StaffPanelAccess::isRestrictedStaff($user) && ! $restrictedAllowed) {
            return false;
        }

        /** @phpstan-ignore-next-line */
        return $user->can(AdminModules::permission(static::$module, $action));
    }
}
