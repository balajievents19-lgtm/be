<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        BlogPost::query()->withTrashed()->forceDelete();
        BlogCategory::query()->withTrashed()->forceDelete();

        $categories = [
            ['name' => 'Wedding Tips', 'description' => 'Practical advice for planning a memorable wedding in Rajasthan.'],
            ['name' => 'Event Planning', 'description' => 'Guides for corporate, private, and destination events.'],
            ['name' => 'Décor Ideas', 'description' => 'Stage, floral, and venue decoration inspiration.'],
            ['name' => 'Catering Guides', 'description' => 'Menus, presentation, and hospitality best practices.'],
            ['name' => 'Photography', 'description' => 'Tips for capturing wedding and event moments.'],
        ];

        $categoryModels = [];

        foreach ($categories as $index => $item) {
            $categoryModels[] = BlogCategory::query()->create([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'description' => $item['description'],
                'sort_order' => $index + 1,
                'status' => true,
            ]);
        }

        $titles = [
            'How to Plan a Perfect Rajasthani Wedding',
            'Top 10 Wedding Mandap Décor Trends',
            'Destination Wedding Checklist for Jhunjhunu',
            'Choosing the Right Wedding Caterer',
            'Bridal Entry Ideas That Wow Guests',
            'Corporate Event Planning Timeline',
            'How to Host a Memorable Birthday Party',
            'Outdoor Tent Setup Essentials',
            'Stage Lighting Tips for Evening Events',
            'Mehndi Night Theme Ideas',
            'Floral Arrangements on a Budget',
            'Luxury Reception Décor Inspiration',
            'Modern vs Traditional Stage Designs',
            'How to Style a Wedding Buffet',
            'Live Counters That Impress Guests',
            'Dessert Table Presentation Ideas',
            'Photography Shot List for Weddings',
            'Candid Moments Every Couple Should Capture',
            'Videography Tips for Reception Parties',
            'Family Portrait Posing Guide',
            'Guest Experience Ideas for Indian Weddings',
            'How to Create a Wedding Day Timeline',
            'Rain Contingency Planning for Outdoor Events',
            'Vendor Coordination Best Practices',
            'SEO-Friendly Tips for Event Planning Blogs',
        ];

        $tagSets = [
            ['wedding', 'rajasthan', 'planning'],
            ['decor', 'mandap', 'trends'],
            ['checklist', 'destination', 'wedding'],
            ['catering', 'menu', 'hospitality'],
            ['bridal', 'entry', 'ideas'],
            ['corporate', 'timeline', 'events'],
            ['birthday', 'party', 'celebration'],
            ['tent', 'outdoor', 'setup'],
            ['lighting', 'stage', 'evening'],
            ['mehndi', 'theme', 'decor'],
            ['floral', 'budget', 'decor'],
            ['reception', 'luxury', 'decor'],
            ['stage', 'design', 'modern'],
            ['buffet', 'catering', 'styling'],
            ['live-counter', 'food', 'events'],
            ['dessert', 'presentation', 'wedding'],
            ['photography', 'shot-list', 'wedding'],
            ['candid', 'couple', 'photos'],
            ['videography', 'reception', 'tips'],
            ['family', 'portraits', 'posing'],
            ['guests', 'experience', 'wedding'],
            ['timeline', 'wedding-day', 'planning'],
            ['outdoor', 'rain', 'contingency'],
            ['vendors', 'coordination', 'events'],
            ['seo', 'blogging', 'content'],
        ];

        foreach ($titles as $index => $title) {
            $category = $categoryModels[$index % count($categoryModels)];
            $image = $this->storeSeedImage($index);
            $excerpt = 'Expert guidance from Balaji Royal Events on '.$title.' for celebrations across Rajasthan.';
            $content = '<p>'.$excerpt.'</p><p>Balaji Royal Events helps couples and organizers plan weddings, décor, catering, photography, and entertainment with trusted local expertise.</p><h2>Key Takeaways</h2><ul><li>Plan early and confirm vendors in writing.</li><li>Align décor, catering, and entertainment with your guest experience.</li><li>Work with a local team that understands Rajasthan venues and traditions.</li></ul><p>Contact Balaji Royal Events to customize this approach for your celebration.</p>';

            BlogPost::query()->create([
                'blog_category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'excerpt' => $excerpt,
                'content' => $content,
                'featured_image' => $image,
                'banner_image' => $image,
                'thumbnail' => $image,
                'alt_text' => $title,
                'featured' => $index < 8,
                'homepage_featured' => $index < 4,
                'published_at' => now()->subDays(25 - $index)->setTime(10, 0),
                'status' => true,
                'seo_title' => $title.' | Balaji Royal Events Blog',
                'seo_description' => $excerpt,
                'seo_keywords' => implode(', ', $tagSets[$index]),
                'canonical_url' => 'http://localhost:3000/blog/'.Str::slug($title),
                'opengraph_image' => $image,
                'schema_type' => 'BlogPosting',
                'reading_time' => null,
                'author' => 'Balaji Royal Events',
                'tags' => $tagSets[$index],
            ]);
        }
    }

    private function storeSeedImage(int $index): string
    {
        $filenames = [
            'news-img1.png',
            'news-img2.png',
            'event-img1.jpg',
            'event-img2.jpg',
            'event-img3.jpg',
        ];

        $filename = $filenames[$index % count($filenames)];
        $destination = 'blog/featured/seed-'.$index.'-'.$filename;

        $candidates = [
            base_path('../frontend/public/images/news/'.$filename),
            base_path('../frontend/public/images/event/'.$filename),
            storage_path('app/seed/blog/'.$filename),
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
