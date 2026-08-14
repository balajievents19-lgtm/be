<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Support\Rbac\AdminUserSecurity;
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

        return $data;
    }
}
