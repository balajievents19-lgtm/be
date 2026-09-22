<?php

namespace Tests\Unit;

use App\Models\Concerns\HasPublicStorageUrl;
use Tests\TestCase;

class HasPublicStorageUrlTest extends TestCase
{
    public function test_disk_paths_become_origin_relative_storage_urls(): void
    {
        config(['app.url' => 'https://www.balajiroyalevents.com']);
        config(['filesystems.disks.public.url' => 'https://www.balajiroyalevents.com/storage']);

        $model = new class
        {
            use HasPublicStorageUrl;
        };

        $this->assertSame(
            '/storage/hero-slides/desktop/slider-img.jpg',
            $model->imageUrl('hero-slides/desktop/slider-img.jpg')
        );
        $this->assertSame(
            '/storage/hero-slides/desktop/slider-img.jpg',
            $model->imageUrl('https://www.balajiroyalevents.com/storage/hero-slides/desktop/slider-img.jpg')
        );
        $this->assertSame(
            '/storage/services/featured/photo.jpg?v=2',
            $model->imageUrl('http://127.0.0.1:8000/storage/services/featured/photo.jpg?v=2')
        );
        $this->assertSame('/storage/already.jpg', $model->imageUrl('/storage/already.jpg'));
        $this->assertSame('/images/heading-blackBgimg.png', $model->imageUrl('/images/heading-blackBgimg.png'));
        $this->assertSame(
            'https://www.youtube.com/watch?v=abc',
            $model->imageUrl('https://www.youtube.com/watch?v=abc')
        );
        $this->assertSame(
            'https://www.instagram.com/p/xyz/',
            $model->imageUrl('https://www.instagram.com/p/xyz/')
        );
        $this->assertSame(
            '/storage/settings/brand/logo.png',
            app(\App\Support\Media\ResponsiveImageBuilder::class)->build('settings/brand/logo.png')['src']
        );
        $this->assertNull($model->imageUrl(null));
        $this->assertNull($model->imageUrl(''));
    }
}
