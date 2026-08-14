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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (AdminModules::allPermissions() as $permission) {
            Permission::findOrCreate($permission, AdminModules::GUARD);
        }

        $superAdmin = Role::findOrCreate(AdminModules::ROLE_SUPER_ADMIN, AdminModules::GUARD);
        $contentManager = Role::findOrCreate(AdminModules::ROLE_CONTENT_MANAGER, AdminModules::GUARD);
        $leadManager = Role::findOrCreate(AdminModules::ROLE_LEAD_MANAGER, AdminModules::GUARD);
        $newsletterManager = Role::findOrCreate(AdminModules::ROLE_NEWSLETTER_MANAGER, AdminModules::GUARD);

        $superAdmin->syncPermissions(AdminModules::allPermissions());
        $contentManager->syncPermissions(AdminModules::websitePermissions());
        $leadManager->syncPermissions(AdminModules::leadManagerPermissions());
        $newsletterManager->syncPermissions(AdminModules::newsletterManagerPermissions());

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
}
