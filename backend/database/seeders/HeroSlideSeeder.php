<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'title' => 'Celebrate Your Special Day',
                'title_highlight' => 'Special Day',
                'subtitle' => 'Trusted wedding & event management across Rajasthan',
                'button_text' => 'Contact Us',
                'button_url' => '/contact',
                'source' => 'slider-img.jpg',
                'overlay_opacity' => 35,
                'text_alignment' => 'center',
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Corporate & Private Events',
                'title_highlight' => 'Events',
                'subtitle' => 'Conferences, parties, and celebrations planned with care',
                'button_text' => 'Our Services',
                'button_url' => '/services',
                'source' => 'slider-img2.jpg',
                'overlay_opacity' => 40,
                'text_alignment' => 'center',
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Unforgettable Moments',
                'title_highlight' => 'Moments',
                'subtitle' => 'Decor, catering, entertainment — all under one roof',
                'button_text' => 'View Gallery',
                'button_url' => '/gallery',
                'source' => 'slider-img3.jpg',
                'overlay_opacity' => 45,
                'text_alignment' => 'center',
                'status' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($slides as $slide) {
            $source = $slide['source'];
            unset($slide['source']);

            $desktopPath = $this->storeSeedImage($source, 'hero-slides/desktop');
            $mobilePath = $this->storeSeedImage($source, 'hero-slides/mobile');

            HeroSlide::query()->updateOrCreate(
                ['sort_order' => $slide['sort_order']],
                array_merge($slide, [
                    'desktop_image' => $desktopPath,
                    'mobile_image' => $mobilePath,
                    'video_url' => null,
                ])
            );
        }
    }

    private function storeSeedImage(string $filename, string $directory): string
    {
        $candidates = [
            base_path('../frontend/public/images/banner/'.$filename),
            storage_path('app/seed/hero/'.$filename),
        ];

        $sourcePath = collect($candidates)->first(fn (string $path) => File::exists($path));

        if (! $sourcePath) {
            $placeholder = $this->createPlaceholderPng();
            $path = $directory.'/'.$filename;

            Storage::disk('public')->put($path, $placeholder);

            return $path;
        }

        $path = $directory.'/'.$filename;
        Storage::disk('public')->put($path, File::get($sourcePath));

        return $path;
    }

    private function createPlaceholderPng(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==') ?: '';
    }
}
