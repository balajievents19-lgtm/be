<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\Rbac\AdminModules;
use App\Support\Rbac\AdminUserSecurity;
use Database\Seeders\RBACSeeder;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RBACSeeder::class);
    }

    private function userWithRole(string $role, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'email' => strtolower(str_replace(' ', '.', $role)).'.'.uniqid('', true).'@balaji.test',
        ], $overrides));
        $user->assignRole($role);

        return $user;
    }

    public function test_super_admin_can_access_users_resource(): void
    {
        $super = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);

        $this->actingAs($super);
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(UserResource::canCreate());

        $this->get(UserResource::getUrl('index'))->assertOk();
        $this->get(UserResource::getUrl('create'))->assertOk();
    }

    public function test_super_admin_can_create_user_with_hashed_password_and_roles(): void
    {
        $super = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $role = Role::findByName(AdminModules::ROLE_LEAD_MANAGER, AdminModules::GUARD);

        $this->actingAs($super);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New Lead Manager',
                'email' => 'new.lead@balaji.test',
                'password' => 'Password1!secure',
                'password_confirmation' => 'Password1!secure',
                'roles' => [$role->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::query()->where('email', 'new.lead@balaji.test')->first();
        $this->assertNotNull($created);
        $this->assertTrue(Hash::check('Password1!secure', $created->password));
        $this->assertNotSame('Password1!secure', $created->password);
        $this->assertTrue($created->hasRole(AdminModules::ROLE_LEAD_MANAGER));
        $this->assertArrayNotHasKey('password', $created->toArray());
    }

    public function test_blank_password_on_edit_preserves_existing_password(): void
    {
        $super = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $target = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER, [
            'email' => 'keep.password@balaji.test',
            'password' => 'OriginalPass1!',
        ]);
        $originalHash = $target->password;

        $this->actingAs($super);

        Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Name',
                'email' => 'keep.password@balaji.test',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $target->refresh();
        $this->assertSame('Updated Name', $target->name);
        $this->assertSame($originalHash, $target->password);
        $this->assertTrue(Hash::check('OriginalPass1!', $target->password));
    }

    public function test_super_admin_can_change_password_and_roles(): void
    {
        $super = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $target = $this->userWithRole(AdminModules::ROLE_NEWSLETTER_MANAGER, [
            'email' => 'change.roles@balaji.test',
        ]);
        $contentRole = Role::findByName(AdminModules::ROLE_CONTENT_MANAGER, AdminModules::GUARD);

        $this->actingAs($super);

        Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
            ->fillForm([
                'name' => $target->name,
                'email' => $target->email,
                'password' => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
                'roles' => [$contentRole->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $target->refresh();
        $this->assertTrue(Hash::check('NewPassword1!', $target->password));
        $this->assertTrue($target->hasRole(AdminModules::ROLE_CONTENT_MANAGER));
        $this->assertFalse($target->hasRole(AdminModules::ROLE_NEWSLETTER_MANAGER));
    }

    public function test_user_cannot_delete_self(): void
    {
        $super = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);

        $this->assertFalse($super->can('delete', $super));
        $this->assertFalse(AdminUserSecurity::canDelete($super, $super));
    }

    public function test_last_super_admin_cannot_be_deleted(): void
    {
        $actor = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN, [
            'email' => 'actor.for.delete@balaji.test',
        ]);
        $last = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN, [
            'email' => 'last.super@balaji.test',
        ]);

        // Demote every Super Admin except $last.
        User::role(AdminModules::ROLE_SUPER_ADMIN)
            ->whereKeyNot($last->id)
            ->each(fn (User $user) => $user->syncRoles([AdminModules::ROLE_CONTENT_MANAGER]));

        $this->assertTrue(AdminUserSecurity::isLastSuperAdmin($last->fresh()));
        $this->assertFalse($actor->fresh()->can('delete', $last->fresh()));
    }

    public function test_last_super_admin_cannot_be_demoted(): void
    {
        $actor = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN, [
            'email' => 'demote.actor@balaji.test',
        ]);

        // Leave only $actor as Super Admin.
        User::role(AdminModules::ROLE_SUPER_ADMIN)
            ->whereKeyNot($actor->id)
            ->each(fn (User $user) => $user->syncRoles([AdminModules::ROLE_CONTENT_MANAGER]));

        $this->assertTrue(AdminUserSecurity::isLastSuperAdmin($actor->fresh()));

        $contentRole = Role::findByName(AdminModules::ROLE_CONTENT_MANAGER, AdminModules::GUARD);

        $this->actingAs($actor);

        Livewire::test(EditUser::class, ['record' => $actor->getRouteKey()])
            ->fillForm([
                'name' => $actor->name,
                'email' => $actor->email,
                'roles' => [$contentRole->id],
            ])
            ->call('save');

        $this->assertTrue(
            $actor->fresh()->hasRole(AdminModules::ROLE_SUPER_ADMIN),
            'Last Super Admin must keep the Super Admin role after demotion attempt.'
        );
        $this->assertSame(1, User::role(AdminModules::ROLE_SUPER_ADMIN)->count());
    }

    public function test_non_super_admin_cannot_assign_roles_or_super_admin(): void
    {
        $content = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);
        $target = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER, [
            'email' => 'target.lead@balaji.test',
        ]);

        $this->assertFalse($content->can('assignRoles'));
        $this->assertFalse(AdminUserSecurity::canManageRoles($content));
        $this->assertFalse($content->can('update', $target));
    }

    public function test_content_lead_newsletter_managers_cannot_access_users(): void
    {
        foreach ([
            AdminModules::ROLE_CONTENT_MANAGER,
            AdminModules::ROLE_LEAD_MANAGER,
            AdminModules::ROLE_NEWSLETTER_MANAGER,
        ] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user);

            $this->assertFalse(UserResource::canViewAny());
            $this->assertFalse(UserResource::canCreate());
            $this->assertFalse($user->can('users.view'));
            $this->assertFalse($user->can('users.create'));
            $this->assertFalse($user->can('users.update'));
            $this->assertFalse($user->can('users.delete'));

            $this->get(UserResource::getUrl('index'))->assertForbidden();
            $this->get(UserResource::getUrl('create'))->assertForbidden();
        }
    }

    public function test_non_authorized_cannot_edit_or_delete_via_direct_url(): void
    {
        $lead = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $target = $this->userWithRole(AdminModules::ROLE_NEWSLETTER_MANAGER, [
            'email' => 'victim.newsletter@balaji.test',
        ]);

        $this->actingAs($lead)
            ->get(UserResource::getUrl('edit', ['record' => $target]))
            ->assertForbidden();

        $this->assertFalse($lead->can('delete', $target));
        $this->assertFalse($lead->can('update', $target));
    }

    public function test_super_admin_can_delete_eligible_non_last_super_admin_user(): void
    {
        $actor = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN, [
            'email' => 'deleter@balaji.test',
        ]);
        // Ensure at least one other Super Admin remains after any delete of a non-super.
        $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN, [
            'email' => 'backup.super@balaji.test',
        ]);

        $target = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER, [
            'email' => 'delete.me@balaji.test',
        ]);

        $this->assertTrue($actor->can('delete', $target));

        $targetId = $target->id;
        $target->delete();

        $this->assertNull(User::query()->find($targetId));
    }

    public function test_panel_access_still_allows_role_users_with_admin_emails_allowlist(): void
    {
        putenv('ADMIN_EMAILS=admin@balaji.test');
        $_ENV['ADMIN_EMAILS'] = 'admin@balaji.test';
        $_SERVER['ADMIN_EMAILS'] = 'admin@balaji.test';
        config(['auth.admin_emails' => 'admin@balaji.test']);

        $manager = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER, [
            'email' => 'panel.lead@example.com',
        ]);

        $panel = \Mockery::mock(Panel::class);
        $this->assertTrue($manager->canAccessPanel($panel));

        putenv('ADMIN_EMAILS=');
        $_ENV['ADMIN_EMAILS'] = '';
        $_SERVER['ADMIN_EMAILS'] = '';
        config(['auth.admin_emails' => '']);
    }
}
