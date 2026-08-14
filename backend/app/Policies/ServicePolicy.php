<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

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
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, Service $service): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, Service $service): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $this->allows($user, 'delete');
    }
}
