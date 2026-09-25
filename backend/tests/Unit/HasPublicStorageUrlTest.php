<?php

namespace Tests\Unit;

use App\Models\Concerns\HasPublicStorageUrl;
use Tests\TestCase;

class HasPublicStorageUrlTest extends TestCase
{
    public function test_disk_paths_become_origin_relative_protected_urls(): void
    {
        config(['app.url' => 'https://www.balajiroyalevents.com']);
        config(['filesystems.disks.public.url' => 'https://www.balajiroyalevents.com/storage']);

        $model = new class
        {
            use HasPublicStorageUrl;
        };

        $this->assertProtectedDisplayUrl(
            $model->imageUrl('hero-slides/desktop/slider-img.jpg'),
            'hero-slides/desktop/slider-img.jpg'
        );
        $this->assertProtectedDisplayUrl(
            $model->imageUrl('https://www.balajiroyalevents.com/storage/hero-slides/desktop/slider-img.jpg'),
            'hero-slides/desktop/slider-img.jpg'
        );

        $withQuery = $model->imageUrl('http://127.0.0.1:8000/storage/services/featured/photo.jpg?v=2');
        $this->assertProtectedDisplayUrl($withQuery, 'services/featured/photo.jpg');
        $this->assertSame('v=2&d=v5', parse_url((string) $withQuery, PHP_URL_QUERY));

        $this->assertProtectedDisplayUrl($model->imageUrl('/storage/already.jpg'), 'already.jpg');
        $this->assertSame('/images/heading-blackBgimg.png', $model->imageUrl('/images/heading-blackBgimg.png'));
        $this->assertSame(
            'https://www.youtube.com/watch?v=abc',
            $model->imageUrl('https://www.youtube.com/watch?v=abc')
        );
        $this->assertSame(
            'https://www.instagram.com/p/xyz/',
            $model->imageUrl('https://www.instagram.com/p/xyz/')
        );
        $this->assertProtectedDisplayUrl(
            app(\App\Support\Media\ResponsiveImageBuilder::class)->build('settings/brand/logo.png')['src'],
            'settings/brand/logo.png'
        );
        $this->assertNull($model->imageUrl(null));
        $this->assertNull($model->imageUrl(''));
    }
}
