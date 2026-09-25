<?php

namespace App\Support\Staff;

use App\Models\User;
use App\Support\Rbac\AdminModules;
use App\Support\Rbac\AdminUserSecurity;
use Illuminate\Support\Collection;

final class StaffContentAccess
{
    public static function isSuperAdmin(?User $user): bool
    {
        return $user !== null && AdminUserSecurity::isSuperAdmin($user);
    }

    public static function canDeleteContent(?User $user): bool
    {
        return self::isSuperAdmin($user);
    }

    public static function canApprove(?User $user): bool
    {
        return self::isSuperAdmin($user);
    }

    public static function canPublish(?User $user): bool
    {
        return self::isSuperAdmin($user);
    }

    public static function canAccessService(?User $user, int $serviceId): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        $user->loadMissing('serviceAccesses', 'galleryCategoryAccesses');

        return $user->serviceAccesses
            ->first(fn ($row) => (int) $row->service_id === $serviceId && $row->can_access) !== null;
    }

    public static function canCreateInService(?User $user, int $serviceId): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        $user->loadMissing('serviceAccesses', 'galleryCategoryAccesses');

        $row = $user->serviceAccesses->first(fn ($row) => (int) $row->service_id === $serviceId);

        return $row !== null && $row->can_access && $row->can_create;
    }

    public static function canEditOwnInService(?User $user, int $serviceId): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        $user->loadMissing('serviceAccesses', 'galleryCategoryAccesses');

        $row = $user->serviceAccesses->first(fn ($row) => (int) $row->service_id === $serviceId);

        return $row !== null && $row->can_access && $row->can_edit_own;
    }

    public static function canAccessGalleryCategory(?User $user, int $categoryId): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        $user->loadMissing('serviceAccesses', 'galleryCategoryAccesses');

        return $user->galleryCategoryAccesses
            ->first(fn ($row) => (int) $row->gallery_category_id === $categoryId && $row->can_access) !== null;
    }

    public static function canCreateInGalleryCategory(?User $user, int $categoryId): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        $user->loadMissing('serviceAccesses', 'galleryCategoryAccesses');

        $row = $user->galleryCategoryAccesses
            ->first(fn ($row) => (int) $row->gallery_category_id === $categoryId);

        return $row !== null && $row->can_access && $row->can_create;
    }

    public static function canEditOwnInGalleryCategory(?User $user, int $categoryId): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        $user->loadMissing('serviceAccesses', 'galleryCategoryAccesses');

        $row = $user->galleryCategoryAccesses
            ->first(fn ($row) => (int) $row->gallery_category_id === $categoryId);

        return $row !== null && $row->can_access && $row->can_edit_own;
    }

    /**
     * @return list<int>
     */
    public static function accessibleGalleryCategoryIds(User $user): array
    {
        if (self::isSuperAdmin($user)) {
            return [];
        }

        $user->loadMissing('galleryCategoryAccesses');

        return $user->galleryCategoryAccesses
            ->filter(fn ($row) => $row->can_access)
            ->pluck('gallery_category_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public static function accessibleServiceIds(User $user): array
    {
        if (self::isSuperAdmin($user)) {
            return [];
        }

        $user->loadMissing('serviceAccesses');

        return $user->serviceAccesses
            ->filter(fn ($row) => $row->can_access)
            ->pluck('service_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public static function canCreateAnyGalleryContent(?User $user): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        $user->loadMissing('serviceAccesses', 'galleryCategoryAccesses');

        return $user->galleryCategoryAccesses->contains(fn ($row) => $row->can_access && $row->can_create)
            || $user->serviceAccesses->contains(fn ($row) => $row->can_access && $row->can_create);
    }

    public static function hasAssignedContentScope(?User $user): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        return self::accessibleGalleryCategoryIds($user) !== []
            || self::accessibleServiceIds($user) !== [];
    }

    /**
     * @param  Collection<int, \App\Models\Service>|iterable<int>|null  $services
     */
    public static function servicesAreAllowed(?User $user, iterable $serviceIds): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        foreach ($serviceIds as $id) {
            if (! self::canAccessService($user, (int) $id)) {
                return false;
            }
        }

        return true;
    }

    public static function specialistGalleryPermissions(): array
    {
        return [
            AdminModules::permission('gallery', 'view'),
            AdminModules::permission('gallery', 'create'),
            AdminModules::permission('gallery', 'update'),
            AdminModules::permission('services', 'view'),
        ];
    }
}
