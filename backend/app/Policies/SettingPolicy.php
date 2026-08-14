<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use App\Support\Rbac\AdminModules;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasWebsitePermission($user, 'view');
    }

    public function view(User $user, Setting $setting): bool
    {
        return $this->hasWebsitePermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->hasWebsitePermission($user, 'create');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $this->hasWebsitePermission($user, 'update');
    }

    public function delete(User $user, Setting $setting): bool
    {
        return false;
    }

    private function hasWebsitePermission(User $user, string $action): bool
    {
        foreach (AdminModules::WEBSITE as $module) {
            if ($user->can(AdminModules::permission($module, $action))) {
                return true;
            }
        }

        return false;
    }
}
