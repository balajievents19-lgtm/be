<?php

namespace Tests\Support;

use App\Enums\TestimonialType;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\EventOverview;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;

trait CreatesWebsiteContent
{
    protected function seedSettings(array $overrides = []): Setting
    {
        Setting::query()->delete();

        return Setting::query()->create(array_merge([
            'company_name' => 'Balaji Events',
            'company_description' => 'Wedding and event management.',
            'primary_color' => '#f15b22',
            'secondary_color' => '#0e1123',
            'theme_mode' => 'light',
            'robots' => 'index, follow',
            'meta_title' => 'Balaji Events',
            'meta_description' => 'Every event should be perfect.',
            'canonical_url' => 'https://example.test',
        ], $overrides));
    }

    protected function createService(array $overrides = []): Service
    {
        return Service::query()->create(array_merge([
            'name' => 'Wedding Planning',
            'slug' => 'wedding-planning',
            'short_description' => 'Full wedding planning.',
            'full_description' => '<p>Details</p>',
            'featured_image' => 'services/wedding.jpg',
            'status' => true,
            'featured' => true,
            'show_on_homepage' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createGalleryItem(array $overrides = []): GalleryItem
    {
        $category = GalleryCategory::query()->first()
            ?? GalleryCategory::query()->create([
                'name' => 'Weddings',
                'slug' => 'weddings',
                'status' => true,
                'sort_order' => 1,
            ]);

        return GalleryItem::query()->create(array_merge([
            'gallery_category_id' => $category->id,
            'title' => 'Reception Hall',
            'slug' => 'reception-hall',
            'image' => 'gallery/reception.jpg',
            'status' => true,
            'featured' => true,
            'homepage_featured' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createBlogPost(array $overrides = []): BlogPost
    {
        $category = BlogCategory::query()->first()
            ?? BlogCategory::query()->create([
                'name' => 'News',
                'slug' => 'news',
                'status' => true,
                'sort_order' => 1,
            ]);

        return BlogPost::query()->create(array_merge([
            'blog_category_id' => $category->id,
            'title' => 'Season Highlights',
            'slug' => 'season-highlights',
            'excerpt' => 'A short excerpt.',
            'content' => '<p>Full post content.</p>',
            'status' => true,
            'featured' => true,
            'homepage_featured' => true,
            'published_at' => now()->subDay(),
            'schema_type' => 'BlogPosting',
            'author' => 'Admin',
        ], $overrides));
    }

    protected function createFaq(array $overrides = []): Faq
    {
        $category = FaqCategory::query()->first()
            ?? FaqCategory::query()->create([
                'name' => 'General',
                'slug' => 'general',
                'status' => true,
                'sort_order' => 1,
            ]);

        return Faq::query()->create(array_merge([
            'faq_category_id' => $category->id,
            'question' => 'How do I book?',
            'slug' => 'how-do-i-book',
            'answer' => '<p>Call us.</p>',
            'status' => true,
            'featured' => true,
            'homepage_featured' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createEventOverview(array $overrides = []): EventOverview
    {
        return EventOverview::query()->create(array_merge([
            'title' => 'Corporate Events',
            'caption' => 'Professional gatherings',
            'description' => 'We plan corporate events.',
            'image' => 'events/corporate.jpg',
            'status' => true,
            'featured' => true,
            'homepage_featured' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createTestimonial(array $overrides = []): Testimonial
    {
        return Testimonial::query()->create(array_merge([
            'type' => TestimonialType::ClientSays,
            'name' => 'Priya Sharma',
            'quote' => 'Amazing team.',
            'body' => 'They made our day perfect.',
            'status' => true,
            'featured' => true,
            'homepage_featured' => true,
            'sort_order' => 1,
        ], $overrides));
    }
}
