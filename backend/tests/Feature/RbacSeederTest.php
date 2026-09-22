<?php

namespace Tests\Feature;

use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RbacSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_all_permissions_when_model_events_are_silenced(): void
    {
        Model::withoutEvents(fn () => $this->seed(RBACSeeder::class));

        $this->assertSame(
            count(AdminModules::allPermissions()),
            Permission::query()->where('guard_name', AdminModules::GUARD)->count()
        );

        foreach (AdminModules::allPermissions() as $permission) {
            $this->assertTrue(
                Permission::query()
                    ->where('name', $permission)
                    ->where('guard_name', AdminModules::GUARD)
                    ->exists(),
                "Missing permission {$permission} for guard ".AdminModules::GUARD
            );
        }

        $this->assertSame(
            count(AdminModules::allPermissions()),
            Role::findByName(AdminModules::ROLE_SUPER_ADMIN, AdminModules::GUARD)->permissions()->count()
        );
    }

    public function test_seeder_is_idempotent_when_model_events_are_silenced(): void
    {
        Model::withoutEvents(function (): void {
            $this->seed(RBACSeeder::class);
            $this->seed(RBACSeeder::class);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->assertSame(
            count(AdminModules::allPermissions()),
            Permission::query()->where('guard_name', AdminModules::GUARD)->count()
        );

        $this->assertSame(
            count(AdminModules::allPermissions()),
            Role::findByName(AdminModules::ROLE_SUPER_ADMIN, AdminModules::GUARD)->permissions()->count()
        );
    }
}
