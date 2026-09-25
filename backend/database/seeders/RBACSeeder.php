<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac\AdminModules;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RBACSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app()[PermissionRegistrar::class];
        $registrar->forgetCachedPermissions();

        $permissionsByName = [];

        foreach (AdminModules::allPermissions() as $name) {
            $permissionsByName[$name] = Permission::query()->firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => AdminModules::GUARD,
                ]
            );
        }

        $superAdmin = $this->role(AdminModules::ROLE_SUPER_ADMIN);
        $contentManager = $this->role(AdminModules::ROLE_CONTENT_MANAGER);
        $leadManager = $this->role(AdminModules::ROLE_LEAD_MANAGER);
        $newsletterManager = $this->role(AdminModules::ROLE_NEWSLETTER_MANAGER);

        // DatabaseSeeder uses WithoutModelEvents, so Spatie's saved/deleted cache
        // listeners never run. Reload after writes so syncPermissions does not
        // look up names against a snapshot taken before these rows existed.
        $registrar->forgetCachedPermissions();

        $superAdmin->syncPermissions(array_values($permissionsByName));
        $contentManager->syncPermissions($this->resolvePermissions(
            AdminModules::contentManagerPermissions(),
            $permissionsByName
        ));
        $leadManager->syncPermissions($this->resolvePermissions(
            AdminModules::leadManagerPermissions(),
            $permissionsByName
        ));
        $newsletterManager->syncPermissions($this->resolvePermissions(
            AdminModules::newsletterManagerPermissions(),
            $permissionsByName
        ));

        $specialistPermissions = $this->resolvePermissions(
            \App\Support\Staff\StaffContentAccess::specialistGalleryPermissions(),
            $permissionsByName
        );
        $this->role(AdminModules::ROLE_STAFF_MEMBER)->syncPermissions($specialistPermissions);
        foreach (AdminModules::SPECIALIST_ROLES as $roleName) {
            $this->role($roleName)->syncPermissions($specialistPermissions);
        }

        $admin = User::query()->where('email', 'admin@balajievents.test')->first();

        if ($admin === null) {
            $this->command?->error('RBACSeeder: intended Admin user admin@balajievents.test was not found. Super Admin role was NOT assigned.');
            $this->command?->warn('Create/seed the Admin user first (AdminUserSeeder), then re-run RBACSeeder.');

            return;
        }

        if (! $admin->hasRole(AdminModules::ROLE_SUPER_ADMIN)) {
            $admin->assignRole($superAdmin);
        }

        $this->command?->info('RBACSeeder: permissions/roles synced; Super Admin assigned to admin@balajievents.test.');
    }

    private function role(string $name): Role
    {
        return Role::query()->firstOrCreate(
            [
                'name' => $name,
                'guard_name' => AdminModules::GUARD,
            ]
        );
    }

    /**
     * @param  list<string>  $names
     * @param  array<string, Permission>  $permissionsByName
     * @return list<Permission>
     */
    private function resolvePermissions(array $names, array $permissionsByName): array
    {
        return array_values(array_map(
            fn (string $name): Permission => $permissionsByName[$name],
            $names
        ));
    }
}
