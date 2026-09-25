<?php

namespace App\Support\Staff;

use App\Models\GalleryCategory;
use App\Models\Service;
use App\Models\User;

final class StaffAccessSync
{
    /**
     * @param  array<int|string, array<string, mixed>>  $serviceAccess
     * @param  array<int|string, array<string, mixed>>  $categoryAccess
     */
    public static function sync(User $user, array $serviceAccess, array $categoryAccess): void
    {
        $serviceIds = Service::query()->pluck('id')->all();
        $user->serviceAccesses()->whereNotIn('service_id', $serviceIds ?: [0])->delete();

        foreach ($serviceIds as $serviceId) {
            $row = $serviceAccess[$serviceId] ?? $serviceAccess[(string) $serviceId] ?? [];
            $access = (bool) ($row['can_access'] ?? false);
            $create = $access && (bool) ($row['can_create'] ?? false);
            $editOwn = $access && (bool) ($row['can_edit_own'] ?? false);

            if (! $access && ! $create && ! $editOwn) {
                $user->serviceAccesses()->where('service_id', $serviceId)->delete();

                continue;
            }

            $user->serviceAccesses()->updateOrCreate(
                ['service_id' => $serviceId],
                [
                    'can_access' => $access,
                    'can_create' => $create,
                    'can_edit_own' => $editOwn,
                ]
            );
        }

        $categoryIds = GalleryCategory::query()->pluck('id')->all();
        $user->galleryCategoryAccesses()->whereNotIn('gallery_category_id', $categoryIds ?: [0])->delete();

        foreach ($categoryIds as $categoryId) {
            $row = $categoryAccess[$categoryId] ?? $categoryAccess[(string) $categoryId] ?? [];
            $access = (bool) ($row['can_access'] ?? false);
            $create = $access && (bool) ($row['can_create'] ?? false);
            $editOwn = $access && (bool) ($row['can_edit_own'] ?? false);

            if (! $access && ! $create && ! $editOwn) {
                $user->galleryCategoryAccesses()->where('gallery_category_id', $categoryId)->delete();

                continue;
            }

            $user->galleryCategoryAccesses()->updateOrCreate(
                ['gallery_category_id' => $categoryId],
                [
                    'can_access' => $access,
                    'can_create' => $create,
                    'can_edit_own' => $editOwn,
                ]
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function formState(User $user): array
    {
        $user->loadMissing('serviceAccesses', 'galleryCategoryAccesses');
        $serviceAccess = [];
        foreach ($user->serviceAccesses as $row) {
            $serviceAccess[$row->service_id] = [
                'can_access' => $row->can_access,
                'can_create' => $row->can_create,
                'can_edit_own' => $row->can_edit_own,
            ];
        }
        $categoryAccess = [];
        foreach ($user->galleryCategoryAccesses as $row) {
            $categoryAccess[$row->gallery_category_id] = [
                'can_access' => $row->can_access,
                'can_create' => $row->can_create,
                'can_edit_own' => $row->can_edit_own,
            ];
        }

        return [
            'service_access' => $serviceAccess,
            'category_access' => $categoryAccess,
        ];
    }
}
