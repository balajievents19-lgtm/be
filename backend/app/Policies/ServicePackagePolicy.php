<?php

namespace App\Policies;

use App\Models\ServicePackage;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class ServicePackagePolicy
{
    use ChecksModulePermission;

    protected static string $module = 'packages';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, ServicePackage $servicePackage): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, ServicePackage $servicePackage): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, ServicePackage $servicePackage): bool
    {
        return $this->allows($user, 'delete');
    }
}
