<?php

namespace App\Filament\Concerns;

use App\Models\GalleryItem;
use App\Support\Rbac\AdminUserSecurity;
use App\Support\Staff\ContentModeration;
use App\Support\Staff\StaffContentAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

trait AppliesStaffContentRules
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function applyStaffCreateData(array $data): array
    {
        $user = Auth::user();
        if ($user === null) {
            throw new AuthorizationException('Unauthenticated.');
        }
        $this->assertAssignedTargets($data, null);
        $submit = property_exists($this, 'submitForReview') ? (bool) $this->submitForReview : true;
        if (StaffContentAccess::isSuperAdmin($user)) {
            $data['created_by'] = $data['created_by'] ?? $user->id;
            $data['updated_by'] = $user->id;
            if (($data['status'] ?? true) && blank($data['moderation_status'] ?? null)) {
                $data['moderation_status'] = \App\Enums\ContentModerationStatus::Published->value;
            }

            return $data;
        }

        return ContentModeration::prepareStaffCreate($user, $data, $submit);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function applyStaffUpdateData(array $data, Model $record): array
    {
        $user = Auth::user();
        if ($user === null) {
            throw new AuthorizationException('Unauthenticated.');
        }
        $this->assertAssignedTargets($data, $record);
        $submit = property_exists($this, 'submitForReview') ? (bool) $this->submitForReview : true;
        if (StaffContentAccess::isSuperAdmin($user)) {
            $data['updated_by'] = $user->id;

            return $data;
        }

        return ContentModeration::prepareStaffUpdate($user, $record, $data, $submit);
    }

    protected function notifyModerationIfNeeded(Model $record): void
    {
        $user = Auth::user();
        if ($user === null || StaffContentAccess::isSuperAdmin($user)) {
            return;
        }

        ContentModeration::notifySuperAdmins(
            $record,
            $user,
            (bool) $record->getAttribute('brand_review_required')
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function assertAssignedTargets(array $data, ?Model $record): void
    {
        $user = Auth::user();
        if ($user === null || StaffContentAccess::isSuperAdmin($user)) {
            return;
        }

        $categoryId = $data['gallery_category_id'] ?? $record?->getAttribute('gallery_category_id');
        if ($categoryId) {
            $creating = $record === null;
            $ok = $creating
                ? StaffContentAccess::canCreateInGalleryCategory($user, (int) $categoryId)
                : StaffContentAccess::canEditOwnInGalleryCategory($user, (int) $categoryId);
            if (! $ok) {
                throw ValidationException::withMessages([
                    'gallery_category_id' => 'You are not assigned to this gallery category.',
                ]);
            }
        }

        $serviceId = $data['service_id'] ?? $record?->getAttribute('service_id');
        if ($serviceId) {
            $creating = $record === null;
            $ok = $creating
                ? StaffContentAccess::canCreateInService($user, (int) $serviceId)
                : StaffContentAccess::canEditOwnInService($user, (int) $serviceId);
            if (! $ok) {
                throw ValidationException::withMessages([
                    'service_id' => 'You are not assigned to this service.',
                ]);
            }
        }

        $serviceIds = $data['services'] ?? null;
        if (is_array($serviceIds) && $serviceIds !== []) {
            if (! StaffContentAccess::servicesAreAllowed($user, $serviceIds)) {
                throw ValidationException::withMessages([
                    'services' => 'You cannot assign an unauthorized service.',
                ]);
            }
        }
    }

    protected function staffCanDelete(): bool
    {
        return AdminUserSecurity::isSuperAdmin(Auth::user());
    }
}
