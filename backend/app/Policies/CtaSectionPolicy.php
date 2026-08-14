<?php

namespace App\Policies;

use App\Models\CtaSection;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class CtaSectionPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'home';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, CtaSection $ctaSection): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, CtaSection $ctaSection): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, CtaSection $ctaSection): bool
    {
        return $this->allows($user, 'delete');
    }
}
