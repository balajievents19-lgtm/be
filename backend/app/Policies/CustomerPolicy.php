<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class CustomerPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'leads';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return false;
    }
}
