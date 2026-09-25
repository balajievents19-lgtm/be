<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\Rbac\AdminModules;
use App\Support\Rbac\AdminUserSecurity;
use App\Support\Staff\StaffAccessSync;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => Auth::user()?->can('delete', $this->getRecord()) ?? false)
                ->before(function (DeleteAction $action): void {
                    /** @var User $record */
                    $record = $this->getRecord();

                    if (AdminUserSecurity::isLastSuperAdmin($record)) {
                        Notification::make()
                            ->title('Cannot delete the last Super Admin')
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['password_confirmation']);

        // Blank password must not overwrite the existing hash (already dehydrated=false when empty).
        if (! AdminUserSecurity::canManageRoles(Auth::user())) {
            unset($data['roles']);
        }

        unset($data['service_access'], $data['category_access']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $record */
        $record = $this->getRecord();

        return array_merge($data, StaffAccessSync::formState($record), [
            'staff_department' => collect(AdminModules::SPECIALIST_ROLES)
                ->first(fn (string $name): bool => $record->hasRole($name)),
        ]);
    }

    protected function afterSave(): void
    {
        if (! AdminUserSecurity::canManageRoles(Auth::user())) {
            return;
        }

        /** @var User $record */
        $record = $this->getRecord();
        StaffAccessSync::sync(
            $record,
            $this->data['service_access'] ?? [],
            $this->data['category_access'] ?? []
        );

        $department = $this->data['staff_department'] ?? null;
        if (is_string($department) && $department !== '') {
            $record->assignRole($department);
        }
    }

    protected function beforeSave(): void
    {
        /** @var User $record */
        $record = $this->getRecord();

        // Prefer raw Livewire form data; getState() may dehydrate relationship keys differently.
        $roleIds = $this->data['roles'] ?? null;

        if ($roleIds === null) {
            return;
        }

        if (! is_array($roleIds)) {
            $roleIds = [];
        }

        if (! AdminUserSecurity::canManageRoles(Auth::user())) {
            Notification::make()
                ->title('Cannot change roles')
                ->body('Only a Super Admin can assign or change roles.')
                ->danger()
                ->send();

            $this->halt();
        }

        if (AdminUserSecurity::wouldRemoveLastSuperAdmin($record, $roleIds)) {
            Notification::make()
                ->title('Cannot demote the last Super Admin')
                ->body('Assign Super Admin to another user first, or keep the Super Admin role on this account.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
