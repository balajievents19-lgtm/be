<?php

namespace App\Policies;

use App\Models\HeroSlide;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class HeroSlidePolicy
{
    use ChecksModulePermission;

    protected static string $module = 'slider';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, HeroSlide $heroSlide): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, HeroSlide $heroSlide): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, HeroSlide $heroSlide): bool
    {
        return $this->allows($user, 'delete');
    }
}
