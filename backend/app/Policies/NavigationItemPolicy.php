<?php

namespace App\Policies;

use App\Models\NavigationItem;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class NavigationItemPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'header';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, NavigationItem $navigationItem): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, NavigationItem $navigationItem): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, NavigationItem $navigationItem): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, NavigationItem $navigationItem): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, NavigationItem $navigationItem): bool
    {
        return $this->allows($user, 'delete');
    }
}
