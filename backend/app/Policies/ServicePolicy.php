<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;
use App\Support\Staff\StaffContentAccess;

class ServicePolicy
{
    use ChecksModulePermission;

    protected static string $module = 'services';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, Service $service): bool
    {
        if (! $this->allows($user, 'view')) {
            return false;
        }

        return StaffContentAccess::isSuperAdmin($user)
            || StaffContentAccess::canAccessService($user, (int) $service->id);
    }

    public function create(User $user): bool
    {
        return StaffContentAccess::isSuperAdmin($user) && $this->allows($user, 'create');
    }

    public function update(User $user, Service $service): bool
    {
        if (StaffContentAccess::isSuperAdmin($user)) {
            return true;
        }

        return $this->allows($user, 'update') && StaffContentAccess::canAccessService($user, (int) $service->id);
    }

    public function delete(User $user, Service $service): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function restore(User $user, Service $service): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }
}
