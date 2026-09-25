<?php

namespace Tests\Feature;

use App\Filament\Clusters\GalleryCluster;
use App\Filament\Clusters\HomeCluster;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\MediaApprovals;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Filament\Resources\HeroSlides\HeroSlideResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\StaffPosts\StaffPostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\User;
use App\Support\Rbac\AdminModules;
use App\Support\Staff\StaffAccessSync;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class StaffAdminVisibilityTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RBACSeeder::class);
        $this->seedSettings();
    }

    public function test_staff_member_sees_only_staff_post_resources(): void
    {
        $staff = $this->staffMember();
        $this->actingAs($staff);

        $this->assertTrue(StaffPostResource::canAccess());
        $this->assertFalse(Dashboard::canAccess());
        $this->assertFalse(HomeCluster::canAccess());
        $this->assertFalse(GalleryCluster::canAccess());
        $this->assertFalse(GalleryItemResource::canAccess());
        $this->assertFalse(ServiceResource::canAccess());
        $this->assertFalse(HeroSlideResource::canViewAny());
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(MediaApprovals::canAccess());
        $this->assertSame(['My Posts', 'Create Post'], array_map(
            fn ($item) => $item->getLabel(),
            StaffPostResource::getNavigationItems()
        ));
    }

    public function test_staff_member_urls_are_forbidden_except_own_posts(): void
    {
        $staff = $this->staffMember();

        $this->actingAs($staff)
            ->get(StaffPostResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(StaffPostResource::getUrl('create'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(GalleryItemResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(HeroSlideResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(ServiceResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_staff_cannot_open_other_staff_posts_by_id(): void
    {
        $category = GalleryCategory::query()->create(['name' => 'Tent', 'slug' => 'tent-vis', 'status' => true]);
        $service = $this->createService(['name' => 'Tent', 'slug' => 'tent-vis-svc']);
        $amit = $this->staffMember('Amit');
        $rohit = $this->staffMember('Rohit');
        $this->grant($amit, $service, $category);
        $this->grant($rohit, $service, $category);

        $theirs = GalleryItem::query()->create([
            'gallery_category_id' => $category->id,
            'title' => 'Rohit tent',
            'slug' => 'rohit-tent-vis',
            'image' => 'gallery/rohit.jpg',
            'created_by' => $rohit->id,
            'status' => false,
        ]);

        $this->actingAs($amit);
        $this->assertFalse($amit->can('view', $theirs));
        $this->assertFalse($amit->can('update', $theirs));
        $this->assertFalse($amit->can('delete', $theirs));
        $this->assertFalse($amit->can('approve', $theirs));

        $this->actingAs($amit)
            ->get(StaffPostResource::getUrl('edit', ['record' => $theirs]))
            ->assertForbidden();
    }

    public function test_staff_query_only_returns_own_assigned_posts(): void
    {
        $tent = GalleryCategory::query()->create(['name' => 'Tent', 'slug' => 'tent-scope', 'status' => true]);
        $dj = GalleryCategory::query()->create(['name' => 'DJ', 'slug' => 'dj-scope', 'status' => true]);
        $amit = $this->staffMember('Scope Amit');
        $rohit = $this->staffMember('Scope Rohit');
        $this->grant($amit, $this->createService(['name' => 'Tent S', 'slug' => 'tent-s']), $tent);

        $own = GalleryItem::query()->create([
            'gallery_category_id' => $tent->id,
            'title' => 'Amit tent',
            'slug' => 'amit-tent-scope',
            'image' => 'gallery/amit.jpg',
            'created_by' => $amit->id,
            'status' => false,
        ]);
        GalleryItem::query()->create([
            'gallery_category_id' => $tent->id,
            'title' => 'Rohit tent',
            'slug' => 'rohit-tent-scope',
            'image' => 'gallery/rohit.jpg',
            'created_by' => $rohit->id,
            'status' => false,
        ]);
        GalleryItem::query()->create([
            'gallery_category_id' => $dj->id,
            'title' => 'Amit dj leak',
            'slug' => 'amit-dj-scope',
            'image' => 'gallery/dj.jpg',
            'created_by' => $amit->id,
            'status' => false,
        ]);

        $this->actingAs($amit);
        $ids = StaffPostResource::getEloquentQuery()->pluck('id')->all();

        $this->assertSame([$own->id], $ids);
    }

    public function test_super_admin_keeps_full_admin_access(): void
    {
        $admin = User::factory()->create(['email' => 'sa.vis@balaji.test']);
        $admin->assignRole(AdminModules::ROLE_SUPER_ADMIN);
        $this->actingAs($admin);

        $this->assertTrue(Dashboard::canAccess());
        $this->assertTrue(HomeCluster::canAccess());
        $this->assertTrue(GalleryItemResource::canAccess());
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(MediaApprovals::canAccess());
        $this->assertFalse(StaffPostResource::canAccess());
    }

    private function staffMember(string $name = 'Staff Member'): User
    {
        $user = User::factory()->create([
            'email' => strtolower(str_replace(' ', '.', $name)).'.'.fake()->unique()->numerify('######').'@balaji.test',
            'name' => $name,
        ]);
        $user->assignRole(AdminModules::ROLE_STAFF_MEMBER);

        return $user;
    }

    private function grant(User $user, Service $service, GalleryCategory $category): void
    {
        StaffAccessSync::sync($user, [
            $service->id => ['can_access' => true, 'can_create' => true, 'can_edit_own' => true],
        ], [
            $category->id => ['can_access' => true, 'can_create' => true, 'can_edit_own' => true],
        ]);
        $user->unsetRelation('serviceAccesses');
        $user->unsetRelation('galleryCategoryAccesses');
    }
}
