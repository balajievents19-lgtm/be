<?php

namespace App\Support\Rbac;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Privilege-sensitive guards for Admin Users management.
 * Reuses Spatie roles — does not invent a second ACL.
 */
final class AdminUserSecurity
{
    public static function isSuperAdmin(User $user): bool
    {
        return $user->hasRole(AdminModules::ROLE_SUPER_ADMIN);
    }

    public static function superAdminCount(): int
    {
        return User::role(AdminModules::ROLE_SUPER_ADMIN)->count();
    }

    public static function isLastSuperAdmin(User $user): bool
    {
        if (! self::isSuperAdmin($user)) {
            return false;
        }

        return self::superAdminCount() <= 1;
    }

    /**
     * Only Super Admins may assign / change roles in Admin Users.
     */
    public static function canManageRoles(?User $actor): bool
    {
        return $actor !== null && self::isSuperAdmin($actor);
    }

    /**
     * @param  list<int|string>|null  $roleIds
     */
    public static function roleSelectionIncludesSuperAdmin(?array $roleIds): bool
    {
        if ($roleIds === null || $roleIds === []) {
            return false;
        }

        $superAdminRole = Role::findByName(AdminModules::ROLE_SUPER_ADMIN, AdminModules::GUARD);

        foreach ($roleIds as $roleId) {
            if ((int) $roleId === (int) $superAdminRole->id) {
                return true;
            }
            if (is_string($roleId) && $roleId === AdminModules::ROLE_SUPER_ADMIN) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when saving would leave zero Super Admins.
     *
     * @param  list<int|string>|null  $incomingRoleIds
     */
    public static function wouldRemoveLastSuperAdmin(User $target, ?array $incomingRoleIds): bool
    {
        if (! self::isLastSuperAdmin($target)) {
            return false;
        }

        return ! self::roleSelectionIncludesSuperAdmin($incomingRoleIds);
    }

    public static function canDelete(?User $actor, User $target): bool
    {
        if ($actor === null) {
            return false;
        }

        if ($actor->is($target)) {
            return false;
        }

        if (self::isLastSuperAdmin($target)) {
            return false;
        }

        return $actor->can(AdminModules::permission('users', 'delete'))
            || self::isSuperAdmin($actor);
    }
}
