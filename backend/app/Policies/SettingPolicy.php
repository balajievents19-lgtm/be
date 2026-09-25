<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use App\Support\Rbac\AdminUserSecurity;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return AdminUserSecurity::isSuperAdmin($user);
    }

    public function view(User $user, Setting $setting): bool
    {
        return AdminUserSecurity::isSuperAdmin($user);
    }

    public function create(User $user): bool
    {
        return AdminUserSecurity::isSuperAdmin($user);
    }

    public function update(User $user, Setting $setting): bool
    {
        return AdminUserSecurity::isSuperAdmin($user);
    }

    public function delete(User $user, Setting $setting): bool
    {
        return false;
    }
}
