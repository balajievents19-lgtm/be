<?php

namespace App\Support\Staff;

use App\Models\User;
use App\Support\Rbac\AdminModules;
use App\Support\Rbac\AdminUserSecurity;

final class StaffPanelAccess
{
    public static function isRestrictedStaff(?User $user): bool
    {
        if ($user === null || AdminUserSecurity::isSuperAdmin($user)) {
            return false;
        }

        if ($user->hasRole(AdminModules::ROLE_STAFF_MEMBER)) {
            return true;
        }

        return $user->hasAnyRole(AdminModules::SPECIALIST_ROLES)
            && ! $user->hasRole(AdminModules::ROLE_CONTENT_MANAGER);
    }

    public static function isContentManager(?User $user): bool
    {
        return $user !== null
            && $user->hasRole(AdminModules::ROLE_CONTENT_MANAGER)
            && ! self::isRestrictedStaff($user)
            && ! AdminUserSecurity::isSuperAdmin($user);
    }
}
