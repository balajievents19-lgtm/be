<?php

namespace App\Policies;

use App\Models\GalleryItem;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;
use App\Support\Staff\StaffContentAccess;
use App\Support\Staff\StaffPanelAccess;

class GalleryItemPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'gallery';

    protected const ALLOW_RESTRICTED_STAFF = true;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, GalleryItem $galleryItem): bool
    {
        if (! $this->allows($user, 'view')) {
            return false;
        }

        if (StaffContentAccess::isSuperAdmin($user)) {
            return true;
        }

        if (StaffPanelAccess::isRestrictedStaff($user)) {
            return $galleryItem->isOwnedBy($user)
                && StaffContentAccess::canAccessGalleryCategory($user, (int) $galleryItem->gallery_category_id);
        }

        return StaffContentAccess::canAccessGalleryCategory($user, (int) $galleryItem->gallery_category_id);
    }

    public function create(User $user): bool
    {
        if (! $this->allows($user, 'create')) {
            return false;
        }

        return StaffContentAccess::canCreateAnyGalleryContent($user);
    }

    public function update(User $user, GalleryItem $galleryItem): bool
    {
        if (StaffContentAccess::isSuperAdmin($user)) {
            return true;
        }
        if (! $this->allows($user, 'update')) {
            return false;
        }
        if (! $galleryItem->isOwnedBy($user)) {
            return false;
        }

        return StaffContentAccess::canEditOwnInGalleryCategory($user, (int) $galleryItem->gallery_category_id);
    }

    public function delete(User $user, GalleryItem $galleryItem): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function restore(User $user, GalleryItem $galleryItem): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function forceDelete(User $user, GalleryItem $galleryItem): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function approve(User $user, GalleryItem $galleryItem): bool
    {
        return StaffContentAccess::canApprove($user);
    }
}
