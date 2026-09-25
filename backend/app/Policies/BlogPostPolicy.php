<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;
use App\Support\Staff\StaffContentAccess;

class BlogPostPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'blog';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, BlogPost $blogPost): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, BlogPost $blogPost): bool
    {
        if (StaffContentAccess::isSuperAdmin($user)) {
            return true;
        }

        return $this->allows($user, 'update') && $blogPost->isOwnedBy($user);
    }

    public function delete(User $user, BlogPost $blogPost): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function restore(User $user, BlogPost $blogPost): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function approve(User $user, BlogPost $blogPost): bool
    {
        return StaffContentAccess::canApprove($user);
    }
}
