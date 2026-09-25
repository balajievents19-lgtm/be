<?php

namespace Tests\Feature;

use App\Enums\ContentModerationStatus;
use App\Filament\Resources\Users\Schemas\UserContentAccessFields;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\User;
use App\Notifications\MediaApprovalRequired;
use App\Support\Rbac\AdminModules;
use App\Support\Staff\StaffAccessSync;
use App\Support\Staff\StaffContentAccess;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class StaffContentWorkflowTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seed(RBACSeeder::class);
        $this->seedSettings();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['email' => 'super.'.$this->n().'@balaji.test', 'name' => 'Super Admin']);
        $user->assignRole(AdminModules::ROLE_SUPER_ADMIN);

        return $user;
    }

    private function staff(string $role, string $name = 'Staff'): User
    {
        $user = User::factory()->create([
            'email' => strtolower(str_replace([' ', '/'], ['.', '-'], $name)).'.'.$this->n().'@balaji.test',
            'name' => $name,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function n(): string
    {
        return (string) fake()->unique()->numerify('######');
    }

    /**
     * @param  array<string, bool>  $flags
     */
    private function grantCategory(User $user, GalleryCategory $category, array $flags = []): void
    {
        StaffAccessSync::sync($user, [], [
            $category->id => array_merge([
                'can_access' => true,
                'can_create' => true,
                'can_edit_own' => true,
            ], $flags),
        ]);
        $user->unsetRelation('galleryCategoryAccesses');
    }

    /**
     * @param  array<string, bool>  $flags
     */
    private function grantService(User $user, Service $service, array $flags = []): void
    {
        StaffAccessSync::sync($user, [
            $service->id => array_merge([
                'can_access' => true,
                'can_create' => true,
                'can_edit_own' => true,
            ], $flags),
        ], []);
        $user->unsetRelation('serviceAccesses');
    }

    public function test_super_admin_full_access(): void
    {
        $admin = $this->superAdmin();
        $item = $this->createGalleryItem();

        $this->assertTrue($admin->can('create', GalleryItem::class));
        $this->assertTrue($admin->can('update', $item));
        $this->assertTrue($admin->can('delete', $item));
        $this->assertTrue($admin->can('approve', $item));
        $this->assertTrue(StaffContentAccess::canPublish($admin));
    }

    public function test_staff_assigned_service_and_category_can_create(): void
    {
        $category = GalleryCategory::query()->create(['name' => 'Photography', 'slug' => 'photography-cat', 'status' => true]);
        $service = $this->createService(['name' => 'Photography & Cinematic Films', 'slug' => 'photo-films']);
        $staff = $this->staff('Photographer', 'Amit');
        StaffAccessSync::sync($staff, [
            $service->id => ['can_access' => true, 'can_create' => true, 'can_edit_own' => true],
        ], [
            $category->id => ['can_access' => true, 'can_create' => true, 'can_edit_own' => true],
        ]);
        $staff->unsetRelation('galleryCategoryAccesses');
        $staff->unsetRelation('serviceAccesses');

        $this->actingAs($staff)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $category->id,
            'title' => 'Amit shoot',
            'image' => 'gallery/amit.jpg',
            'services' => [$service->id],
        ])->assertCreated()
            ->assertJsonPath('data.moderation_status', ContentModerationStatus::PendingReview->value)
            ->assertJsonPath('data.status', false);
    }

    public function test_staff_cannot_create_unauthorized_service_or_category(): void
    {
        $allowed = GalleryCategory::query()->create(['name' => 'DJ Nights', 'slug' => 'dj-nights', 'status' => true]);
        $blocked = GalleryCategory::query()->create(['name' => 'Catering', 'slug' => 'catering-cat', 'status' => true]);
        $blockedService = $this->createService(['name' => 'Catering & Live Counters', 'slug' => 'catering-live']);
        $staff = $this->staff('DJ/Sound Manager', 'DJ');
        $this->grantCategory($staff, $allowed);

        $this->actingAs($staff)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $blocked->id,
            'title' => 'Unauthorized category',
            'image' => 'gallery/x.jpg',
        ])->assertForbidden();

        $this->actingAs($staff)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $allowed->id,
            'title' => 'Wrong service',
            'image' => 'gallery/x.jpg',
            'services' => [$blockedService->id],
        ])->assertForbidden();
    }

    public function test_staff_can_edit_and_update_own_content_only(): void
    {
        $category = GalleryCategory::query()->create(['name' => 'Tent', 'slug' => 'tent-cat', 'status' => true]);
        $amit = $this->staff('Tent Manager', 'Amit');
        $rohit = $this->staff('Tent Manager', 'Rohit');
        $this->grantCategory($amit, $category);
        $this->grantCategory($rohit, $category);

        $a = $this->actingAs($amit)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $category->id,
            'title' => 'Post A',
            'image' => 'gallery/a.jpg',
            'seo_title' => 'SEO A',
        ])->assertCreated()->json('data.id');

        $b = $this->actingAs($rohit)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $category->id,
            'title' => 'Post B',
            'image' => 'gallery/b.jpg',
        ])->assertCreated()->json('data.id');

        $this->actingAs($amit)->putJson('/api/staff-content/gallery-items/'.$a, [
            'title' => 'Post A updated',
            'seo_title' => 'SEO A2',
        ])->assertOk()->assertJsonPath('data.title', 'Post A updated');

        $this->actingAs($amit)->putJson('/api/staff-content/gallery-items/'.$b, [
            'title' => 'Hijack',
        ])->assertForbidden();

        $this->actingAs($rohit)->putJson('/api/staff-content/gallery-items/'.$a, [
            'title' => 'Hijack',
        ])->assertForbidden();
    }

    public function test_staff_cannot_delete_own_or_other_content_via_api(): void
    {
        $category = GalleryCategory::query()->create(['name' => 'Mehndi', 'slug' => 'mehndi-cat', 'status' => true]);
        $staff = $this->staff('Bridal/Mehndi Manager', 'Mehndi Staff');
        $other = $this->staff('Bridal/Mehndi Manager', 'Other Mehndi');
        $this->grantCategory($staff, $category);
        $this->grantCategory($other, $category);

        $own = $this->actingAs($staff)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $category->id,
            'title' => 'Own',
            'image' => 'gallery/own.jpg',
        ])->json('data.id');
        $theirs = $this->actingAs($other)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $category->id,
            'title' => 'Theirs',
            'image' => 'gallery/theirs.jpg',
        ])->json('data.id');

        $this->actingAs($staff)->deleteJson('/api/staff-content/gallery-items/'.$own)->assertForbidden();
        $this->actingAs($staff)->deleteJson('/api/staff-content/gallery-items/'.$theirs)->assertForbidden();
        $this->assertDatabaseHas('gallery_items', ['id' => $own, 'deleted_at' => null]);
    }

    public function test_staff_cannot_publish_or_bypass_category_id(): void
    {
        $allowed = GalleryCategory::query()->create(['name' => 'Decorator & Florist', 'slug' => 'decorator', 'status' => true]);
        $blocked = GalleryCategory::query()->create(['name' => 'SFX Effects', 'slug' => 'sfx', 'status' => true]);
        $staff = $this->staff('Decorator', 'Decor');
        $this->grantCategory($staff, $allowed);

        $this->actingAs($staff)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $allowed->id,
            'title' => 'Decor post',
            'image' => 'gallery/decor.jpg',
            'status' => true,
        ])->assertForbidden();

        $created = $this->actingAs($staff)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $allowed->id,
            'title' => 'Decor post',
            'image' => 'gallery/decor.jpg',
        ])->assertCreated();

        $this->actingAs($staff)->putJson('/api/staff-content/gallery-items/'.$created->json('data.id'), [
            'status' => true,
        ])->assertForbidden();

        $this->actingAs($staff)->putJson('/api/staff-content/gallery-items/'.$created->json('data.id'), [
            'gallery_category_id' => $blocked->id,
            'title' => 'Moved',
        ])->assertForbidden();

        $this->actingAs($staff)->postJson('/api/staff-content/gallery-items/'.$created->json('data.id').'/approve')
            ->assertForbidden();
    }

    public function test_moderation_public_api_and_super_admin_review(): void
    {
        Notification::fake();
        $admin = $this->superAdmin();
        $live = $this->createGalleryItem(['slug' => 'existing-live', 'title' => 'Existing live']);
        $this->assertSame('published', $live->fresh()->moderation_status);

        $category = GalleryCategory::query()->create(['name' => 'Wedding Planner', 'slug' => 'wedding-planner-cat', 'status' => true]);
        $staff = $this->staff('Wedding Planner', 'Planner');
        $this->grantCategory($staff, $category);

        $pendingId = $this->actingAs($staff)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $category->id,
            'title' => 'New planner media',
            'slug' => 'new-planner-media',
            'image' => 'gallery/new.jpg',
        ])->assertCreated()->json('data.id');

        Notification::assertSentTo($admin, MediaApprovalRequired::class);

        $this->getJson('/api/gallery')->assertOk();
        $slugs = collect($this->getJson('/api/gallery')->json('data'))->pluck('slug');
        $this->assertTrue($slugs->contains('existing-live'));
        $this->assertFalse($slugs->contains('new-planner-media'));

        $this->actingAs($admin)->postJson('/api/staff-content/gallery-items/'.$pendingId.'/reject')->assertOk();
        $this->assertFalse(collect($this->getJson('/api/gallery')->json('data'))->pluck('slug')->contains('new-planner-media'));

        $this->actingAs($admin)->postJson('/api/staff-content/gallery-items/'.$pendingId.'/approve')->assertOk()
            ->assertJsonPath('data.moderation_status', 'published')
            ->assertJsonPath('data.status', true);

        Cache::flush();
        $this->assertTrue(collect($this->getJson('/api/gallery')->json('data'))->pluck('slug')->contains('new-planner-media'));

        $this->actingAs($admin)->deleteJson('/api/staff-content/gallery-items/'.$pendingId)->assertOk();
        $this->assertSoftDeleted('gallery_items', ['id' => $pendingId]);
    }

    public function test_brand_review_and_new_service_category_assignment(): void
    {
        Notification::fake();
        $admin = $this->superAdmin();
        $category = GalleryCategory::query()->create(['name' => 'Hospitality', 'slug' => 'hospitality-cat', 'status' => true]);
        $staff = $this->staff('Event Manager', 'Hospitality Manager');
        $this->grantCategory($staff, $category);

        $this->actingAs($staff)->postJson('/api/staff-content/gallery-items', [
            'gallery_category_id' => $category->id,
            'title' => 'Guest watermark banner',
            'image' => 'gallery/watermark.jpg',
        ])->assertCreated()->assertJsonPath('data.moderation_status', 'brand_review');

        Notification::assertSentTo($admin, MediaApprovalRequired::class);

        $newService = $this->createService(['name' => 'New Royal Service', 'slug' => 'new-royal-service']);
        $newCategory = GalleryCategory::query()->create(['name' => 'New Gallery Category', 'slug' => 'new-gallery-category', 'status' => true]);

        $this->assertTrue(Service::query()->whereKey($newService->id)->exists());
        $this->assertTrue(GalleryCategory::query()->whereKey($newCategory->id)->exists());
        $sections = UserContentAccessFields::sections();
        $this->assertNotEmpty($sections);
        $this->assertSame('Staff Access & Permissions', $sections[0]->getHeading());

        $fresh = $this->staff('Photographer', 'Unassigned');
        $this->assertFalse(StaffContentAccess::canAccessService($fresh, $newService->id));
        $this->assertFalse(StaffContentAccess::canAccessGalleryCategory($fresh, $newCategory->id));

        StaffAccessSync::sync($fresh, [
            $newService->id => ['can_access' => true, 'can_create' => true, 'can_edit_own' => true],
        ], [
            $newCategory->id => ['can_access' => true, 'can_create' => true, 'can_edit_own' => true],
        ]);
        $fresh->unsetRelation('serviceAccesses');
        $fresh->unsetRelation('galleryCategoryAccesses');
        $this->assertTrue(StaffContentAccess::canAccessService($fresh, $newService->id));
        $this->assertTrue(StaffContentAccess::canCreateInGalleryCategory($fresh, $newCategory->id));
    }

    public function test_existing_super_admin_gallery_and_enquiry_security(): void
    {
        $admin = $this->superAdmin();
        $this->assertTrue($admin->can('users.view'));
        $this->createGalleryItem(['slug' => 'gallery-intact', 'title' => 'Intact']);
        $this->getJson('/api/gallery')->assertOk()->assertJsonFragment(['slug' => 'gallery-intact']);

        $this->postJson('/api/contact', [
            'name' => 'Guest',
            'mobile' => '9876543210',
            'email' => 'guest@example.com',
            'message' => 'Hello',
            'website' => '',
        ])->assertUnauthorized()->assertJsonPath('code', 'customer_auth_required');
    }

    public function test_android_home_external_media_is_approved_published_only(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/staff-content/external-media', [
            'title' => 'Live film',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'media_type' => 'video',
            'provider' => 'youtube',
            'status' => true,
            'homepage_featured' => true,
        ]);

        $category = GalleryCategory::query()->create(['name' => 'Production', 'slug' => 'production-cat', 'status' => true]);
        $staff = $this->staff('Photographer', 'Cam');
        $this->grantCategory($staff, $category);

        $this->actingAs($staff)->postJson('/api/staff-content/external-media', [
            'title' => 'Pending film',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'media_type' => 'video',
            'provider' => 'youtube',
            'gallery_category_id' => $category->id,
        ])->assertCreated();

        Cache::flush();
        $titles = collect($this->getJson('/api/home')->json('data.external_media'))->pluck('title');
        $this->assertTrue($titles->contains('Live film'));
        $this->assertFalse($titles->contains('Pending film'));

        $apiTitles = collect($this->getJson('/api/external-media')->json('data'))->pluck('title');
        $this->assertTrue($apiTitles->contains('Live film'));
        $this->assertFalse($apiTitles->contains('Pending film'));
    }
}
