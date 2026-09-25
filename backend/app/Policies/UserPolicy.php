<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Rbac\AdminModules;
use App\Support\Rbac\AdminUserSecurity;
use App\Support\Staff\StaffPanelAccess;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        if (StaffPanelAccess::isRestrictedStaff($user)) {
            return false;
        }

        return $user->can(AdminModules::permission('users', 'view'));
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(AdminModules::permission('users', 'view'));
    }

    public function create(User $user): bool
    {
        return $user->can(AdminModules::permission('users', 'create'));
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(AdminModules::permission('users', 'update'));
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return false;
        }

        if (AdminUserSecurity::isLastSuperAdmin($model)) {
            return false;
        }

        return $user->can(AdminModules::permission('users', 'delete'));
    }

    /**
     * Role assignment is privilege-sensitive — Super Admin only.
     */
    public function assignRoles(User $user): bool
    {
        return AdminUserSecurity::canManageRoles($user);
    }
}
