<?php

namespace App\Policies;

use App\Models\ExternalMedia;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;
use App\Support\Staff\StaffContentAccess;
use App\Support\Staff\StaffPanelAccess;

class ExternalMediaPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'gallery';

    protected const ALLOW_RESTRICTED_STAFF = true;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, ExternalMedia $externalMedia): bool
    {
        if (! $this->allows($user, 'view')) {
            return false;
        }
        if (StaffContentAccess::isSuperAdmin($user)) {
            return true;
        }
        if (StaffPanelAccess::isRestrictedStaff($user)) {
            return $externalMedia->isOwnedBy($user);
        }
        if ($externalMedia->gallery_category_id) {
            return StaffContentAccess::canAccessGalleryCategory($user, (int) $externalMedia->gallery_category_id);
        }
        if ($externalMedia->service_id) {
            return StaffContentAccess::canAccessService($user, (int) $externalMedia->service_id);
        }

        return $externalMedia->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create') && StaffContentAccess::canCreateAnyGalleryContent($user);
    }

    public function update(User $user, ExternalMedia $externalMedia): bool
    {
        if (StaffContentAccess::isSuperAdmin($user)) {
            return true;
        }
        if (! $this->allows($user, 'update') || ! $externalMedia->isOwnedBy($user)) {
            return false;
        }
        if ($externalMedia->gallery_category_id) {
            return StaffContentAccess::canEditOwnInGalleryCategory($user, (int) $externalMedia->gallery_category_id);
        }
        if ($externalMedia->service_id) {
            return StaffContentAccess::canEditOwnInService($user, (int) $externalMedia->service_id);
        }

        return false;
    }

    public function delete(User $user, ExternalMedia $externalMedia): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function restore(User $user, ExternalMedia $externalMedia): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function forceDelete(User $user, ExternalMedia $externalMedia): bool
    {
        return StaffContentAccess::canDeleteContent($user);
    }

    public function approve(User $user, ExternalMedia $externalMedia): bool
    {
        return StaffContentAccess::canApprove($user);
    }
}
