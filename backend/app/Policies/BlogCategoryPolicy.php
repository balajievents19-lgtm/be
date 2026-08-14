<?php

namespace App\Policies;

use App\Models\BlogCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class BlogCategoryPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'blog';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, BlogCategory $blogCategory): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, BlogCategory $blogCategory): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, BlogCategory $blogCategory): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, BlogCategory $blogCategory): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, BlogCategory $blogCategory): bool
    {
        return $this->allows($user, 'delete');
    }
}
