<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class ServiceCategoryPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'services';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, ServiceCategory $serviceCategory): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, ServiceCategory $serviceCategory): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, ServiceCategory $serviceCategory): bool
    {
        return $this->allows($user, 'delete');
    }
}
