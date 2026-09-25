<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Support\Rbac\AdminUserSecurity;
use App\Support\Staff\StaffAccessSync;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['password_confirmation']);

        // Non-Super Admins must never assign roles (defense in depth).
        if (! AdminUserSecurity::canManageRoles(Auth::user())) {
            unset($data['roles']);
        }

        unset($data['service_access'], $data['category_access']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! AdminUserSecurity::canManageRoles(Auth::user())) {
            return;
        }

        StaffAccessSync::sync(
            $this->getRecord(),
            $this->data['service_access'] ?? [],
            $this->data['category_access'] ?? []
        );

        $department = $this->data['staff_department'] ?? null;
        if (is_string($department) && $department !== '') {
            $this->getRecord()->assignRole($department);
        }
    }
}
