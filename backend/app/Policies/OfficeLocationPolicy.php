<?php

namespace App\Policies;

use App\Models\OfficeLocation;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class OfficeLocationPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'contact';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, OfficeLocation $officeLocation): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, OfficeLocation $officeLocation): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, OfficeLocation $officeLocation): bool
    {
        return $this->allows($user, 'delete');
    }
}
