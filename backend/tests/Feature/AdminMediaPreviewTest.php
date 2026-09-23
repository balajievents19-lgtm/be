<?php

namespace Tests\Feature;

use App\Filament\Tables\Columns\AdminPreviewImageColumn;
use App\Models\User;
use App\Support\Media\AdminPreviewMedia;
use App\Support\Media\ProtectedMedia;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_use_admin_preview_route(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/brand/logo.png', $this->tinyJpeg());

        $url = AdminPreviewMedia::url('settings/brand/logo.png');
        $this->assertIsString($url);
        $this->assertStringStartsWith(AdminPreviewMedia::URL_PREFIX, $url);
        $this->assertStringNotContainsString('settings/brand/logo.png', $url);

        $this->get($url)->assertForbidden();
        $this->get('/storage/settings/brand/logo.png')->assertForbidden();
    }

    public function test_customer_user_cannot_use_admin_preview_route(): void
    {
        Storage::fake('public');
        $this->seed(RBACSeeder::class);
        Storage::disk('public')->put('settings/brand/logo.png', $this->tinyJpeg());
        config(['auth.admin_emails' => 'admin.preview@balaji.test']);

        $customer = User::factory()->create(['email' => 'preview.customer@example.com']);
        $url = AdminPreviewMedia::url('settings/brand/logo.png');

        $this->actingAs($customer, 'web')->get($url)->assertForbidden();
    }

    public function test_admin_preview_serves_inline_original_bytes(): void
    {
        Storage::fake('public');
        $this->seed(RBACSeeder::class);

        $binary = $this->tinyJpeg();
        Storage::disk('public')->put('settings/brand/logo.png', $binary);
        Storage::disk('public')->put('gallery/thumbnails/thumb.jpg', $binary);

        config(['auth.admin_emails' => 'admin.preview@balaji.test']);
        $admin = User::factory()->create(['email' => 'admin.preview@balaji.test']);
        $admin->assignRole(AdminModules::ROLE_SUPER_ADMIN);
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));

        $logoUrl = AdminPreviewMedia::url('settings/brand/logo.png');
        $logoResponse = $this->actingAs($admin, 'web')->get($logoUrl);
        $logoResponse->assertOk()->assertHeader('Content-Disposition', 'inline');
        $this->assertSame($binary, $logoResponse->getContent());

        $thumbUrl = AdminPreviewMedia::url('gallery/thumbnails/thumb.jpg');
        $thumbResponse = $this->actingAs($admin, 'web')->get($thumbUrl);
        $thumbResponse->assertOk();
        $this->assertSame($binary, $thumbResponse->getContent());

        $this->get('/protected-media/'.ProtectedMedia::token('settings/brand/logo.png'))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline');
    }

    public function test_file_upload_payload_uses_admin_preview_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/brand/logo.png', $this->tinyJpeg());

        $upload = FileUpload::make('logo')->disk('public')->visibility('public');
        $payload = AdminPreviewMedia::uploadedFilePayload($upload, 'settings/brand/logo.png', null);

        $this->assertIsArray($payload);
        $this->assertStringStartsWith(AdminPreviewMedia::URL_PREFIX, (string) $payload['url']);
        $this->assertStringNotContainsString('/storage/', (string) $payload['url']);
        $this->assertStringNotContainsString('settings/brand/logo.png', (string) $payload['url']);
        $this->assertSame('settings/brand/logo.png', AdminPreviewMedia::pathFromToken(basename((string) parse_url((string) $payload['url'], PHP_URL_PATH))));
    }

    public function test_gallery_image_column_uses_admin_preview_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gallery/previews/hall.jpg', $this->tinyJpeg());

        $column = AdminPreviewImageColumn::make('image')->disk('public');
        $url = $column->getImageUrl('gallery/previews/hall.jpg');

        $this->assertIsString($url);
        $this->assertStringStartsWith(AdminPreviewMedia::URL_PREFIX, $url);
        $this->assertStringNotContainsString('gallery/previews/hall.jpg', $url);
        $this->assertStringNotContainsString('/storage/', $url);
    }

    private function tinyJpeg(): string
    {
        $image = imagecreatetruecolor(48, 32);
        $this->assertNotFalse($image);
        $fill = imagecolorallocate($image, 241, 91, 34);
        imagefilledrectangle($image, 0, 0, 47, 31, $fill);
        ob_start();
        imagejpeg($image, null, 80);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        return $binary;
    }
}
