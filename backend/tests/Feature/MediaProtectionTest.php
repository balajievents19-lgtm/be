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

    public function test_display_negotiates_avif_webp_jpeg_and_keeps_logos_as_png(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->seed(RBACSeeder::class);

        $photo = $this->photoPng(500, 500);
        Storage::disk('public')->put('gallery/hall.png', $photo);
        $logo = $this->tinyPng();
        Storage::disk('public')->put('settings/brand/logo.png', $logo);

        $photoToken = basename((string) parse_url((string) PublicStorageUrl::make('gallery/hall.png'), PHP_URL_PATH));
        $logoToken = basename((string) parse_url((string) PublicStorageUrl::make('settings/brand/logo.png'), PHP_URL_PATH));

        $pngBaseline = app(\App\Support\Media\DisplayImageFactory::class)
            ->make($photo, 'gallery/hall.png', \App\Support\Media\DisplayImageFactory::MODE_DISPLAY, \App\Support\Media\DisplayImageFactory::FORMAT_PNG);
        $this->assertSame('image/png', $pngBaseline['mime']);

        $avif = $this->withHeaders(['Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8'])
            ->get('/protected-media/'.$photoToken);
        $avif->assertOk()
            ->assertHeader('Content-Type', 'image/avif')
            ->assertHeader('Vary', 'Accept');
        $this->assertLessThan(strlen($pngBaseline['contents']), strlen((string) $avif->getContent()));
        $this->assertLessThan((int) (strlen($pngBaseline['contents']) * 0.85), strlen((string) $avif->getContent()));
        $this->assertGreaterThan(2000, strlen((string) $avif->getContent()));
        $this->assertNotSame($photo, $avif->getContent());
        $this->assertSame($photo, Storage::disk('public')->get('gallery/hall.png'));

        $decoded = imagecreatefromstring((string) $avif->getContent());
        $this->assertInstanceOf(\GdImage::class, $decoded);
        $this->assertSame(500, imagesx($decoded));
        $this->assertSame(500, imagesy($decoded));
        imagedestroy($decoded);

        $webp = $this->withHeaders(['Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8'])
            ->get('/protected-media/'.$photoToken);
        $webp->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->assertLessThan(strlen($pngBaseline['contents']), strlen((string) $webp->getContent()));
        $this->assertNotSame($avif->headers->get('etag'), $webp->headers->get('etag'));

        $jpeg = $this->withHeaders(['Accept' => 'image/jpeg,image/*,*/*;q=0.8'])
            ->get('/protected-media/'.$photoToken);
        $jpeg->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        $etag = (string) $avif->headers->get('etag');
        $this->withHeaders([
            'Accept' => 'image/avif,image/webp',
            'If-None-Match' => $etag,
        ])->get('/protected-media/'.$photoToken)->assertStatus(304);

        $this->withHeaders(['Accept' => 'image/avif,image/webp'])
            ->get('/protected-media/'.$logoToken)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
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

    private function tinyPng(): string
    {
        $image = imagecreatetruecolor(64, 64);
        $this->assertNotFalse($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, 63, 63, $transparent);
        imagealphablending($image, true);
        $ink = imagecolorallocate($image, 241, 91, 34);
        imagefilledellipse($image, 32, 32, 28, 28, $ink);
        ob_start();
        imagepng($image);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        return $binary;
    }

    private function photoPng(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        $this->assertNotFalse($image);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $n = (($x * 73) ^ ($y * 151) ^ ($x * $y * 19)) & 255;
                $color = imagecolorallocate($image, $n, 255 - $n, ($x + $y) % 220);
                imagesetpixel($image, $x, $y, $color);
            }
        }
        ob_start();
        imagepng($image, null, 6);
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
