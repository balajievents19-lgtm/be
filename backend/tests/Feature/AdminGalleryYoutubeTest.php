<?php

namespace Tests\Feature;

use App\Enums\GalleryMediaType;
use App\Enums\GalleryVideoSource;
use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\EditGalleryItem;
use App\Models\GalleryItem;
use App\Models\User;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class AdminGalleryYoutubeTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RBACSeeder::class);
        config(['auth.admin_emails' => 'gallery.youtube@balaji.test']);
    }

    public function test_admin_can_create_and_edit_a_youtube_gallery_video(): void
    {
        $category = $this->createGalleryItem()->category;
        $admin = User::factory()->create(['email' => 'gallery.youtube@balaji.test']);
        $admin->assignRole(AdminModules::ROLE_SUPER_ADMIN);
        $this->actingAs($admin);

        Livewire::test(CreateGalleryItem::class)
            ->fillForm([
                'gallery_category_id' => $category->id,
                'media_type' => GalleryMediaType::Video->value,
                'title' => 'Haldi Film',
                'slug' => 'haldi-film-admin',
                'video_source' => GalleryVideoSource::Youtube->value,
                'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = GalleryItem::query()->where('slug', 'haldi-film-admin')->first();
        $this->assertNotNull($item);
        $this->assertTrue($item->isVideo());
        $this->assertSame(GalleryVideoSource::Youtube, $item->video_source);
        $this->assertSame('https://youtu.be/dQw4w9WgXcQ', $item->video_url);

        Livewire::test(EditGalleryItem::class, ['record' => $item->getRouteKey()])
            ->fillForm([
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            $item->fresh()->video_url
        );
    }

    public function test_admin_rejects_a_youtube_url_without_a_video_id(): void
    {
        $category = $this->createGalleryItem()->category;
        $admin = User::factory()->create(['email' => 'gallery.youtube@balaji.test']);
        $admin->assignRole(AdminModules::ROLE_SUPER_ADMIN);
        $this->actingAs($admin);

        Livewire::test(CreateGalleryItem::class)
            ->fillForm([
                'gallery_category_id' => $category->id,
                'media_type' => GalleryMediaType::Video->value,
                'title' => 'Broken Film',
                'slug' => 'broken-film',
                'video_source' => GalleryVideoSource::Youtube->value,
                'video_url' => 'https://www.youtube.com/channel/UCxxxxxxxx',
                'status' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['video_url']);
    }
}
