<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        putenv('ADMIN_EMAILS=');
        $_ENV['ADMIN_EMAILS'] = '';
        $_SERVER['ADMIN_EMAILS'] = '';

        parent::tearDown();
    }

    public function test_empty_admin_emails_allows_non_production_access(): void
    {
        config(['app.env' => 'local']);
        putenv('ADMIN_EMAILS=');
        $_ENV['ADMIN_EMAILS'] = '';
        $_SERVER['ADMIN_EMAILS'] = '';

        $user = User::factory()->create(['email' => 'dev@example.com']);
        $panel = Mockery::mock(Panel::class);

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_admin_emails_allowlist_is_enforced(): void
    {
        putenv('ADMIN_EMAILS=admin@balaji.test,ops@balaji.test');
        $_ENV['ADMIN_EMAILS'] = 'admin@balaji.test,ops@balaji.test';
        $_SERVER['ADMIN_EMAILS'] = 'admin@balaji.test,ops@balaji.test';

        $allowed = User::factory()->create(['email' => 'admin@balaji.test']);
        $denied = User::factory()->create(['email' => 'other@example.com']);
        $panel = Mockery::mock(Panel::class);

        $this->assertTrue($allowed->canAccessPanel($panel));
        $this->assertFalse($denied->canAccessPanel($panel));
    }

    public function test_role_assigned_users_can_access_panel_when_not_on_allowlist(): void
    {
        $this->seed(RBACSeeder::class);

        putenv('ADMIN_EMAILS=admin@balaji.test');
        $_ENV['ADMIN_EMAILS'] = 'admin@balaji.test';
        $_SERVER['ADMIN_EMAILS'] = 'admin@balaji.test';

        $manager = User::factory()->create(['email' => 'lead.manager@example.com']);
        $manager->assignRole(AdminModules::ROLE_LEAD_MANAGER);

        $panel = Mockery::mock(Panel::class);

        $this->assertTrue($manager->canAccessPanel($panel));
    }
}
