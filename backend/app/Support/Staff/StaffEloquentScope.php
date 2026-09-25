<?php

namespace App\Support\Staff;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class StaffEloquentScope
{
    public static function galleryItems(Builder $query, User $user): Builder
    {
        if (StaffContentAccess::isSuperAdmin($user)) {
            return $query;
        }

        if (StaffPanelAccess::isRestrictedStaff($user)) {
            $ids = StaffContentAccess::accessibleGalleryCategoryIds($user);

            return $query
                ->where('created_by', $user->id)
                ->whereIn('gallery_category_id', $ids !== [] ? $ids : [0]);
        }

        $ids = StaffContentAccess::accessibleGalleryCategoryIds($user);

        return $query->whereIn('gallery_category_id', $ids !== [] ? $ids : [0]);
    }

    public static function externalMedia(Builder $query, User $user): Builder
    {
        if (StaffContentAccess::isSuperAdmin($user)) {
            return $query;
        }

        $categoryIds = StaffContentAccess::accessibleGalleryCategoryIds($user);
        $serviceIds = StaffContentAccess::accessibleServiceIds($user);

        if (StaffPanelAccess::isRestrictedStaff($user)) {
            return $query
                ->where('created_by', $user->id)
                ->where(function (Builder $builder) use ($categoryIds, $serviceIds): void {
                    if ($categoryIds !== []) {
                        $builder->orWhereIn('gallery_category_id', $categoryIds);
                    }
                    if ($serviceIds !== []) {
                        $builder->orWhereIn('service_id', $serviceIds);
                    }
                    if ($categoryIds === [] && $serviceIds === []) {
                        $builder->whereRaw('1 = 0');
                    }
                });
        }

        return $query->where(function (Builder $builder) use ($user, $categoryIds, $serviceIds): void {
            $builder->where('created_by', $user->id);
            if ($categoryIds !== []) {
                $builder->orWhereIn('gallery_category_id', $categoryIds);
            }
            if ($serviceIds !== []) {
                $builder->orWhereIn('service_id', $serviceIds);
            }
        });
    }

    public static function blogPosts(Builder $query, User $user): Builder
    {
        if (StaffContentAccess::isSuperAdmin($user)) {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }
}
