<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        Faq::query()->withTrashed()->forceDelete();
        FaqCategory::query()->withTrashed()->forceDelete();

        $categories = [
            ['name' => 'General', 'sort_order' => 1],
            ['name' => 'Services', 'sort_order' => 2],
            ['name' => 'Bookings', 'sort_order' => 3],
        ];

        $categoryModels = [];

        foreach ($categories as $item) {
            $categoryModels[$item['name']] = FaqCategory::query()->create([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'sort_order' => $item['sort_order'],
                'status' => true,
            ]);
        }

        $faqs = [
            [
                'category' => 'General',
                'question' => 'How can I contact Balaji Royal Events?',
                'answer' => '<p>You can call us at +91-9462577065. Email balajievents19@gmail.com.</p>',
                'homepage' => true,
            ],
            [
                'category' => 'General',
                'question' => 'Where is your office located?',
                'answer' => '<p>Shop No.15, Road No.3, Opposite : Jamuna Resort Jhunjhunu (Rajasthan)</p>',
                'homepage' => true,
            ],
            [
                'category' => 'Services',
                'question' => 'What services do you provide?',
                'answer' => '<p>Our services include wedding planning, décor, catering, photography, DJ, mehndi, cakes and entertainment across Rajasthan.</p>',
                'homepage' => true,
            ],
            [
                'category' => 'Services',
                'question' => 'Do you manage weddings in Rajasthan?',
                'answer' => '<p>Balaji Royal Events is a trusted wedding management company in India. We provide many different services in Rajasthan and help couples celebrate their special day in a grand way.</p>',
                'homepage' => true,
            ],
            [
                'category' => 'Bookings',
                'question' => 'How do I send an inquiry?',
                'answer' => '<p>Use the Contact Form on the Contact us page with your name, mobile, email, subject, and message. Our team will follow up shortly.</p>',
                'homepage' => true,
            ],
            [
                'category' => 'Bookings',
                'question' => 'How early should I book my event?',
                'answer' => '<p>We recommend booking at least 2–3 months in advance for weddings and major celebrations so décor, catering, and entertainment can be coordinated properly.</p>',
                'homepage' => false,
            ],
            [
                'category' => 'Services',
                'question' => 'Do you provide custom décor packages?',
                'answer' => '<p>Yes. We customize stage décor, floral arrangements, and venue styling based on your theme, guest count, and budget.</p>',
                'homepage' => false,
            ],
            [
                'category' => 'General',
                'question' => 'What are your working hours?',
                'answer' => '<p>Monday – Saturday: 10:00 AM – 7:00 PM. Sunday is by appointment. Please call ahead to confirm availability.</p>',
                'homepage' => false,
            ],
        ];

        foreach ($faqs as $index => $item) {
            $category = $categoryModels[$item['category']];

            Faq::query()->create([
                'faq_category_id' => $category->id,
                'question' => $item['question'],
                'slug' => Str::slug(Str::limit($item['question'], 80, '')),
                'answer' => $item['answer'],
                'featured' => $index < 5,
                'homepage_featured' => $item['homepage'],
                'sort_order' => $index + 1,
                'status' => true,
                'seo_title' => $item['question'].' | Balaji Royal Events FAQ',
                'seo_description' => trim(strip_tags($item['answer'])),
            ]);
        }
    }
}
