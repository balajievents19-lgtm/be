<?php

namespace App\Policies;

use App\Models\ExternalMedia;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class ExternalMediaPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'gallery';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, ExternalMedia $externalMedia): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, ExternalMedia $externalMedia): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, ExternalMedia $externalMedia): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, ExternalMedia $externalMedia): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, ExternalMedia $externalMedia): bool
    {
        return $this->allows($user, 'delete');
    }
}
