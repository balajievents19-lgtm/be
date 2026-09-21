<?php

namespace Database\Seeders;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        GalleryItem::query()->withTrashed()->forceDelete();
        GalleryCategory::query()->withTrashed()->forceDelete();

        $categories = [
            ['name' => 'Wedding', 'description' => 'Wedding ceremonies and celebrations across Rajasthan.'],
            ['name' => 'DJ & Sound', 'description' => 'DJ setups and professional sound for celebrations.'],
            ['name' => 'Tent House', 'description' => 'Premium tent setups for outdoor celebrations.'],
            ['name' => 'Stage Decoration', 'description' => 'Elegant stage décor for ceremonies and receptions.'],
            ['name' => 'Birthday', 'description' => 'Birthday party décor and celebration moments.'],
            ['name' => 'Catering', 'description' => 'Catering presentations and dining arrangements.'],
            ['name' => 'Wedding Planner', 'description' => 'Full wedding planning highlights and coordination.'],
            ['name' => 'Photography', 'description' => 'Wedding and event photography moments.'],
            ['name' => 'Corporate Events', 'description' => 'Corporate gatherings and business celebrations.'],
            ['name' => 'Mehndi', 'description' => 'Mehndi night setups and festive moments.'],
            ['name' => 'Sangeet', 'description' => 'Sangeet night stages, lighting, and performances.'],
            ['name' => 'Other Events', 'description' => 'Additional celebrations managed by Balaji Royal Events.'],
        ];

        $categoryModels = [];

        foreach ($categories as $index => $item) {
            $categoryModels[] = GalleryCategory::query()->create([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'description' => $item['description'],
                'sort_order' => $index + 1,
                'status' => true,
            ]);
        }

        $titles = [
            'Grand Wedding Mandap',
            'Bridal Entry Moments',
            'Reception Celebration',
            'Mehndi Night Setup',
            'Floral Stage Backdrop',
            'Royal Stage Lighting',
            'Traditional Stage Décor',
            'Modern Stage Design',
            'Luxury Outdoor Tent',
            'Garden Tent Arrangement',
            'Night Tent Ambience',
            'VIP Tent Lounge',
            'Buffet Presentation',
            'Live Counter Setup',
            'Dessert Display',
            'Dining Table Styling',
            'Candid Couple Portraits',
            'Ceremony Coverage',
            'Reception Photography',
            'Family Group Moments',
        ];

        foreach ($titles as $index => $title) {
            $category = $categoryModels[$index % count($categoryModels)];
            $image = $this->storeSeedImage($index);

            GalleryItem::query()->create([
                'gallery_category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => $title.' captured by Balaji Royal Events for celebrations across Rajasthan.',
                'image' => $image,
                'thumbnail' => $image,
                'alt_text' => $title,
                'caption' => $title,
                'youtube_url' => $index === 0 ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
                'vimeo_url' => null,
                'featured' => $index < 8,
                'homepage_featured' => $index < 10,
                'sort_order' => $index + 1,
                'status' => true,
                'seo_title' => $title.' | Balaji Royal Events Gallery',
                'seo_description' => 'View '.$title.' from Balaji Royal Events gallery.',
                'opengraph_image' => $image,
            ]);
        }
    }

    private function storeSeedImage(int $index): string
    {
        $filenames = [
            'home3-galleryImg1.jpg',
            'home3-galleryImg2.jpg',
            'home3-galleryImg3.jpg',
            'home3-galleryImg4.jpg',
            'home3-galleryImg5.jpg',
            'home3-galleryImg6.jpg',
            'home3-galleryImg7.jpg',
            'home3-galleryImg8.jpg',
            'home3-galleryImg9.jpg',
            'home3-galleryImg10.jpg',
            'gallery1.jpg',
            'gallery2.jpg',
            'gallery3.jpg',
            'gallery4.jpg',
            'gallery5.jpg',
            'gallery6.jpg',
            'service-gallerImg1.jpg',
            'service-gallerImg2.jpg',
            'service-gallerImg3.jpg',
            'home3-galleryImg1.jpg',
        ];

        $filename = $filenames[$index % count($filenames)];
        $destination = 'gallery/images/'.$filename;

        $candidates = [
            base_path('../frontend/public/images/gallery/'.$filename),
            storage_path('app/seed/gallery/'.$filename),
        ];

        foreach ($candidates as $source) {
            if (File::isFile($source)) {
                Storage::disk('public')->put($destination, File::get($source));

                return $destination;
            }
        }

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Storage::disk('public')->put($destination, $png);

        return $destination;
    }
}
