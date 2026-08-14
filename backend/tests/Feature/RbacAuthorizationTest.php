<?php

namespace Tests\Feature;

use App\Filament\Clusters\GalleryCluster;
use App\Filament\Clusters\HomeCluster;
use App\Filament\Pages\Header\ManageTopBar;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\HeroSlides\HeroSlideResource;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\ContactInquiry;
use App\Models\User;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RBACSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'email' => strtolower(str_replace(' ', '.', $role)).'@balaji.test',
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_super_admin_can_access_all_admin_modules(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);

        $this->assertTrue($user->can('users.view'));
        $this->assertTrue($user->can('leads.view'));
        $this->assertTrue($user->can('newsletter.view'));
        $this->assertTrue($user->can('gallery.view'));
        $this->assertTrue($user->can('home.view'));

        $this->actingAs($user);
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(ContactInquiryResource::canViewAny());
        $this->assertTrue(NewsletterSubscriberResource::canViewAny());
        $this->assertTrue(HeroSlideResource::canViewAny());
        $this->assertTrue(HomeCluster::canAccess());
        $this->assertTrue(GalleryCluster::canAccess());
        $this->assertTrue(ManageTopBar::canAccess());
    }

    public function test_content_manager_website_only(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);
        $this->actingAs($user);

        $this->assertTrue(HomeCluster::canAccess());
        $this->assertTrue(GalleryCluster::canAccess());
        $this->assertTrue(HeroSlideResource::canViewAny());
        $this->assertTrue(ManageTopBar::canAccess());

        $this->assertFalse(ContactInquiryResource::canViewAny());
        $this->assertFalse(NewsletterSubscriberResource::canViewAny());
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse($user->can('leads.view'));
        $this->assertFalse($user->can('newsletter.view'));
        $this->assertFalse($user->can('users.view'));
    }

    public function test_lead_manager_can_view_lead_record(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $lead = ContactInquiry::query()->create([
            'name' => 'View Test Lead',
            'mobile' => '9000000011',
            'message' => 'Quick view test enquiry',
            'status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAs($user);
        $this->assertTrue(ContactInquiryResource::canView($lead));

        $content = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);
        $this->actingAs($content);
        $this->assertFalse(ContactInquiryResource::canView($lead));
    }

    public function test_lead_manager_leads_only_without_delete(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $this->actingAs($user);

        $this->assertTrue(ContactInquiryResource::canViewAny());
        $this->assertTrue(ContactInquiryResource::canCreate());
        $this->assertTrue($user->can('leads.update'));
        $this->assertFalse($user->can('leads.delete'));

        $this->assertFalse(HomeCluster::canAccess());
        $this->assertFalse(NewsletterSubscriberResource::canViewAny());
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(HeroSlideResource::canViewAny());
    }

    public function test_newsletter_manager_newsletter_only_without_delete(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_NEWSLETTER_MANAGER);
        $this->actingAs($user);

        $this->assertTrue(NewsletterSubscriberResource::canViewAny());
        $this->assertTrue($user->can('newsletter.create'));
        $this->assertTrue($user->can('newsletter.update'));
        $this->assertFalse($user->can('newsletter.delete'));

        $this->assertFalse(ContactInquiryResource::canViewAny());
        $this->assertFalse(HomeCluster::canAccess());
        $this->assertFalse(UserResource::canViewAny());
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);

        $this->assertFalse($user->can('delete', $user));
    }

    public function test_authorized_module_pages_return_success(): void
    {
        $lead = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $this->actingAs($lead)
            ->get(ContactInquiryResource::getUrl('index'))
            ->assertOk();

        $newsletter = $this->userWithRole(AdminModules::ROLE_NEWSLETTER_MANAGER);
        $this->actingAs($newsletter)
            ->get(NewsletterSubscriberResource::getUrl('index'))
            ->assertOk();

        $content = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);
        $this->actingAs($content)
            ->get(HeroSlideResource::getUrl('index'))
            ->assertOk();
    }

    public function test_unauthorized_direct_admin_resource_urls_are_denied(): void
    {
        $leadManager = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);

        $this->actingAs($leadManager)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($leadManager)
            ->get(HeroSlideResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($leadManager)
            ->get(NewsletterSubscriberResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_content_manager_cannot_open_leads_url(): void
    {
        $content = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);

        $this->actingAs($content)
            ->get(ContactInquiryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_public_apis_remain_accessible(): void
    {
        $this->postJson('/api/contact', [
            'name' => 'Public Lead',
            'mobile' => '9000000099',
            'message' => 'Public contact still works.',
            'website' => '',
        ])->assertCreated();

        $this->postJson('/api/newsletter', [
            'email' => 'public.newsletter.rbac@example.com',
            'website' => '',
        ])->assertCreated();

        $this->getJson('/api/gallery/categories')->assertOk();
    }

    public function test_rbac_seeder_is_idempotent(): void
    {
        $this->seed(RBACSeeder::class);
        $this->seed(RBACSeeder::class);

        $this->assertSame(
            count(AdminModules::allPermissions()),
            Role::findByName(AdminModules::ROLE_SUPER_ADMIN)->permissions()->count()
        );
    }
}
