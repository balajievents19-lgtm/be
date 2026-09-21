<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Wedding Planning', 'icon' => 'calendar', 'short' => 'Complete wedding planning and coordination for your special day.'],
            ['name' => 'Tent House', 'icon' => 'tent', 'short' => 'Premium tent setups for outdoor weddings and celebrations.'],
            ['name' => 'Stage Decoration', 'icon' => 'sparkles', 'short' => 'Elegant stage décor for ceremonies and receptions.'],
            ['name' => 'Catering', 'icon' => 'utensils', 'short' => 'Delicious catering menus crafted for every occasion.'],
            ['name' => 'DJ & Sound', 'icon' => 'music', 'short' => 'Professional DJ and sound systems for lively events.'],
            ['name' => 'Photography', 'icon' => 'camera', 'short' => 'Wedding and event photography with cinematic coverage.'],
            ['name' => 'Mehndi', 'icon' => 'flower', 'short' => 'Traditional and modern mehndi artists for your celebration.'],
            ['name' => 'Flower Decoration', 'icon' => 'flower-2', 'short' => 'Fresh floral arrangements for venues and stages.'],
            ['name' => 'Bridal Makeup', 'icon' => 'sparkle', 'short' => 'Bridal makeup and hair styling for a flawless look.'],
            ['name' => 'Event Management', 'icon' => 'briefcase', 'short' => 'End-to-end management for corporate and private events.'],
        ];

        foreach ($services as $index => $item) {
            $slug = Str::slug($item['name']);
            $image = $this->storeSeedImage($index);

            Service::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $item['name'],
                    'short_description' => $item['short'],
                    'full_description' => '<p>'.$item['short'].' Balaji Royal Events delivers trusted event services across Rajasthan.</p>',
                    'featured_image' => $image,
                    'banner_image' => $image,
                    'gallery_images' => [$image],
                    'icon' => $item['icon'],
                    'sort_order' => $index + 1,
                    'featured' => $index < 4,
                    'status' => true,
                    'show_on_homepage' => true,
                    'seo_title' => $item['name'].' | Balaji Royal Events',
                    'seo_description' => $item['short'],
                    'seo_keywords' => $item['name'].', Balaji Royal Events, Rajasthan',
                    'opengraph_image' => $image,
                ]
            );
        }
    }

    private function storeSeedImage(int $index): string
    {
        $names = [
            'service-img1.png',
            'service-img2.png',
            'service-img3.png',
            'service-img4.png',
            'service-img5.png',
            'service-img6.png',
            'service-img7.png',
            'service-img8.png',
            'service-img9.png',
            'service-img6.jpg',
        ];

        $filename = $names[$index % count($names)];
        $candidates = [
            base_path('../frontend/public/images/service-img/'.$filename),
            storage_path('app/seed/services/'.$filename),
        ];

        $source = collect($candidates)->first(fn (string $path) => File::exists($path));
        $target = 'services/featured/'.$filename;

        if ($source) {
            Storage::disk('public')->put($target, File::get($source));

            return $target;
        }

        $placeholder = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==') ?: '';
        Storage::disk('public')->put($target, $placeholder);

        return $target;
    }
}
