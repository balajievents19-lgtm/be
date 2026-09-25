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
        Storage::fake('local');
        $this->seed(RBACSeeder::class);

        $binary = $this->tinyJpeg();
        Storage::disk('public')->put('gallery/hall.jpg', $binary);

        $url = PublicStorageUrl::make('gallery/hall.jpg');
        $this->assertProtectedDisplayUrl($url, 'gallery/hall.jpg');

        $token = basename((string) parse_url((string) $url, PHP_URL_PATH));

        $first = $this->get('/protected-media/'.$token);
        $first->assertOk()
            ->assertHeader('Content-Disposition', 'inline');
        $this->assertStringContainsString('max-age=86400', (string) $first->headers->get('Cache-Control'));
        $this->assertStringContainsString('must-revalidate', (string) $first->headers->get('Cache-Control'));

        $etag = (string) $first->headers->get('etag');
        $this->assertNotEmpty($etag);
        $this->assertSame('MISS', $first->headers->get('x-media-cache'));
        $this->assertNotSame($binary, $first->getContent());

        $second = $this->get('/protected-media/'.$token);
        $second->assertOk();
        $this->assertSame('HIT', $second->headers->get('x-media-cache'));
        $this->assertSame($first->getContent(), $second->getContent());

        $this->withHeaders(['If-None-Match' => $etag])
            ->get('/protected-media/'.$token)
            ->assertStatus(304);

        $this->assertSame($binary, Storage::disk('public')->get('gallery/hall.jpg'));

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

    public function test_factory_tiles_watermark_on_common_aspect_ratios(): void
    {
        $factory = app(\App\Support\Media\DisplayImageFactory::class);

        foreach ([
            [640, 960],
            [1200, 800],
            [800, 800],
            [2000, 1200],
        ] as [$w, $h]) {
            $image = imagecreatetruecolor($w, $h);
            $this->assertNotFalse($image);
            $fill = imagecolorallocate($image, 8, 10, 16);
            imagefilledrectangle($image, 0, 0, $w - 1, $h - 1, $fill);
            ob_start();
            imagejpeg($image, null, 90);
            $binary = (string) ob_get_clean();
            imagedestroy($image);

            $rendered = $factory->make($binary, 'gallery/sample.jpg');
            $this->assertNotSame($binary, $rendered['contents']);
            $decoded = imagecreatefromstring($rendered['contents']);
            $this->assertInstanceOf(\GdImage::class, $decoded);
            imagedestroy($decoded);
        }
    }
}
