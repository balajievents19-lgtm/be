<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Media\ProtectedMedia;
use App\Support\Media\PublicStorageUrl;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_route_serves_inline_image_and_storage_is_gated(): void
    {
        Storage::fake('public');
        $this->seed(RBACSeeder::class);

        $binary = $this->tinyJpeg();
        Storage::disk('public')->put('gallery/hall.jpg', $binary);

        $url = PublicStorageUrl::make('gallery/hall.jpg');
        $this->assertProtectedDisplayUrl($url, 'gallery/hall.jpg');

        $token = basename((string) parse_url((string) $url, PHP_URL_PATH));

        $this->get('/protected-media/'.$token)
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline');

        $this->get('/storage/gallery/hall.jpg')->assertForbidden();

        config(['auth.admin_emails' => 'admin.media@balaji.test']);

        $customer = User::factory()->create(['email' => 'guest.customer@example.com']);
        $this->actingAs($customer, 'web')->get('/storage/gallery/hall.jpg')->assertForbidden();

        $admin = User::factory()->create(['email' => 'admin.media@balaji.test']);
        $admin->assignRole(AdminModules::ROLE_SUPER_ADMIN);
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));

        $this->actingAs($admin, 'web')
            ->get('/storage/gallery/hall.jpg')
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline');

        $this->get('/protected-media/not-a-valid-token')->assertNotFound();
        $this->assertNull(ProtectedMedia::pathFromToken('not-a-valid-token'));
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
