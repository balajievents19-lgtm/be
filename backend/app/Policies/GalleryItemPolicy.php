<?php

namespace App\Policies;

use App\Models\GalleryItem;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class GalleryItemPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'gallery';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, GalleryItem $galleryItem): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, GalleryItem $galleryItem): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, GalleryItem $galleryItem): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, GalleryItem $galleryItem): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, GalleryItem $galleryItem): bool
    {
        return $this->allows($user, 'delete');
    }
}
