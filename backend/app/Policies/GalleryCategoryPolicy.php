<?php

namespace App\Policies;

use App\Models\GalleryCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class GalleryCategoryPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'gallery';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, GalleryCategory $galleryCategory): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, GalleryCategory $galleryCategory): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, GalleryCategory $galleryCategory): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, GalleryCategory $galleryCategory): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, GalleryCategory $galleryCategory): bool
    {
        return $this->allows($user, 'delete');
    }
}
