<?php

namespace App\Policies;

use App\Models\Redirect;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class RedirectPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'seo';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, Redirect $redirect): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, Redirect $redirect): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, Redirect $redirect): bool
    {
        return $this->allows($user, 'delete');
    }
}
