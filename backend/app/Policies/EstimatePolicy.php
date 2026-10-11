<?php

namespace App\Policies;

use App\Models\Estimate;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;
use App\Support\Rbac\AdminModules;

class EstimatePolicy
{
    use ChecksModulePermission;

    protected static string $module = 'estimates';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, Estimate $estimate): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, Estimate $estimate): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, Estimate $estimate): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, Estimate $estimate): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, Estimate $estimate): bool
    {
        return $this->allows($user, 'delete');
    }

    public function viewCosting(User $user, Estimate $estimate): bool
    {
        return $this->allows($user, 'view')
            && $user->hasRole(AdminModules::ROLE_SUPER_ADMIN);
    }
}
