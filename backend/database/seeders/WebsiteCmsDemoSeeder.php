<?php

namespace Database\Seeders;

use App\Enums\CtaSectionKey;
use App\Enums\NavigationLinkTarget;
use App\Enums\RedirectStatusCode;
use App\Enums\TestimonialType;
use App\Models\CtaSection;
use App\Models\EventOverview;
use App\Models\GalleryItem;
use App\Models\NavigationItem;
use App\Models\OfficeLocation;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\Setting;
use App\Models\Statistic;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class WebsiteCmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->enrichSettings();
        $this->seedNavigation();
        $this->seedServiceCatalog();
        $this->seedStatistics();
        $this->seedTeam();
        $this->seedTestimonials();
        $this->seedEventOverviews();
        $this->seedCtas();
        $this->seedOffices();
        $this->seedRedirects();
    }

    private function enrichSettings(): void
    {
        $setting = Setting::singleton();

        $setting->fill([
            'company_name' => $setting->company_name ?: 'Balaji Royal Events',
            'company_tagline' => $setting->company_tagline ?: 'Trusted wedding & event management in Rajasthan',
            'company_description' => $setting->company_description ?: 'Balaji Royal Events is a trusted wedding management company in India. We provide many different services in Rajasthan and promise to set the perfect pitch for bride and groom to celebrate their special day in a grand way.',
            'phone' => $setting->phone ?: '+91-9462577065',
            'alternate_phone' => $setting->alternate_phone ?: null,
            'whatsapp' => $setting->whatsapp ?: '+91-9462577065',
            'email' => $setting->email ?: 'balajievents19@gmail.com',
            'support_email' => $setting->support_email ?: 'balajievents19@gmail.com',
            'address' => $setting->address ?: 'Shop No.15, Road No.3, Opposite : Jamuna Resort Jhunjhunu (Rajasthan)',
            'facebook' => $setting->facebook ?: null,
            'instagram' => $setting->instagram,
            'youtube' => $setting->youtube,
            'linkedin' => $setting->linkedin,
            'twitter' => $setting->twitter,
            'working_hours' => $setting->working_hours ?: "Monday – Saturday: 10:00 AM – 7:00 PM\nSunday: By appointment",
            'holiday_text' => $setting->holiday_text ?: 'Closed on major public holidays. Please call ahead to confirm.',
            'emergency_contact' => $setting->emergency_contact ?: '+91-9462577065',
            'footer_about' => $setting->footer_about ?: 'Balaji Royal Events — trusted wedding and event management in Rajasthan.',
            'copyright_text' => $setting->copyright_text ?: 'Copyright © '.date('Y').' - Balaji Royal Events | All Rights Reserved',
            'about_vision' => 'To be Rajasthan’s most trusted wedding and event partner, crafting celebrations that feel royal, warm, and unforgettable.',
            'about_mission' => 'We plan and execute weddings, destination celebrations, corporate gatherings, and family ceremonies with meticulous detail, honest pricing, and hospitality rooted in Rajasthani tradition.',
            'about_journey' => "Balaji Royal Events began in Jhunjhunu with a simple promise: every celebration deserves grandeur without chaos.\n\nOver the years we have planned Royal Wedding Jaipur evenings, Destination Wedding Udaipur weekends, intimate Mehndi and Haldi ceremonies, sparkling Reception nights, Corporate Events, Birthday Celebrations, and Anniversary Events across Rajasthan.\n\nToday our team manages décor, catering, photography, DJ nights, and complete wedding planning under one roof.",
            'header_enabled' => true,
            'top_bar_enabled' => true,
            'sticky_header_enabled' => true,
            'top_bar_text' => 'Call us for destination weddings across Rajasthan',
            'header_cta_label' => 'Book Consultation',
            'header_cta_url' => '/contact',
            'mobile_header_enabled' => true,
            'mobile_menu_style' => 'drawer',
            'hero_search_enabled' => true,
            'hero_search_placeholder' => 'Tell us about your celebration',
            'hero_search_button_label' => 'Get a Free Quote',
            'homepage_seo_title' => 'Balaji Royal Events | Wedding Planning, Décor & Destination Celebrations in Rajasthan',
            'homepage_seo_description' => 'Plan Royal Wedding Jaipur, Destination Wedding Udaipur, Mehndi, Haldi, Reception, Luxury Catering, Photography and DJ Night with Balaji Royal Events.',
            'homepage_seo_keywords' => 'Balaji Royal Events, wedding planner Rajasthan, destination wedding Udaipur, royal wedding Jaipur, event management Jhunjhunu',
            'footer_enabled' => true,
            'footer_newsletter_enabled' => true,
            'footer_social_enabled' => true,
            'sitemap_enabled' => true,
            'google_map_embed' => $setting->google_map_embed ?: '<iframe src="https://maps.google.com/maps?q=Jhunjhunu%20Rajasthan&t=&z=13&ie=UTF8&iwloc=&output=embed" width="600" height="450" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
        ]);

        $setting->save();
    }

    private function seedNavigation(): void
    {
        $items = [
            ['label' => 'Home', 'url' => '/', 'sort_order' => 1, 'show_on_header' => true, 'show_on_footer' => true],
            ['label' => 'About Us', 'url' => '/about', 'sort_order' => 2, 'show_on_header' => true, 'show_on_footer' => true],
            ['label' => 'Services', 'url' => '/services', 'sort_order' => 3, 'show_on_header' => true, 'show_on_footer' => true],
            ['label' => 'Gallery', 'url' => '/gallery', 'sort_order' => 4, 'show_on_header' => true, 'show_on_footer' => true],
            ['label' => 'Packages', 'url' => '/packages', 'sort_order' => 5, 'show_on_header' => true, 'show_on_footer' => true],
            ['label' => 'Blog', 'url' => '/blog', 'sort_order' => 6, 'show_on_header' => true, 'show_on_footer' => true],
            ['label' => 'FAQ’s', 'url' => '/faq', 'sort_order' => 7, 'show_on_header' => true, 'show_on_footer' => true],
            ['label' => 'Contact us', 'url' => '/contact', 'sort_order' => 8, 'show_on_header' => true, 'show_on_footer' => true],
            ['label' => 'Privacy', 'url' => '/privacy-policy', 'sort_order' => 9, 'show_on_header' => false, 'show_on_footer' => true],
        ];

        foreach ($items as $item) {
            NavigationItem::query()->updateOrCreate(
                ['url' => $item['url']],
                [
                    ...$item,
                    'target' => NavigationLinkTarget::SameTab,
                    'status' => true,
                    'is_visible' => true,
                ]
            );
        }
    }

    private function seedServiceCatalog(): void
    {
        $categories = [
            ['name' => 'Wedding Celebrations', 'slug' => 'wedding-celebrations', 'description' => 'End-to-end wedding planning for royal and destination celebrations.', 'icon' => 'calendar', 'sort_order' => 1],
            ['name' => 'Ceremonies', 'slug' => 'ceremonies', 'description' => 'Mehndi, Haldi, Reception and intimate family rituals.', 'icon' => 'flower', 'sort_order' => 2],
            ['name' => 'Production & Décor', 'slug' => 'production-decor', 'description' => 'Stage decorations, flower décor and premium setups.', 'icon' => 'sparkles', 'sort_order' => 3],
            ['name' => 'Hospitality & Media', 'slug' => 'hospitality-media', 'description' => 'Luxury catering, photography and DJ entertainment.', 'icon' => 'camera', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            ServiceCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'status' => true, 'is_visible' => true, 'seo_title' => $category['name'].' | Balaji Royal Events']
            );
        }

        $map = [
            'wedding-planning' => 'wedding-celebrations',
            'tent-house' => 'production-decor',
            'stage-decorations' => 'production-decor',
            'catering' => 'hospitality-media',
            'dj-sound' => 'hospitality-media',
            'photography' => 'hospitality-media',
            'mehndi' => 'ceremonies',
            'flower-decorations' => 'production-decor',
            'bridal-makeup' => 'ceremonies',
            'event-management' => 'wedding-celebrations',
        ];

        foreach ($map as $serviceSlug => $categorySlug) {
            $category = ServiceCategory::query()->where('slug', $categorySlug)->first();
            $service = Service::query()->where('slug', $serviceSlug)->first();
            if ($category && $service) {
                $service->update(['service_category_id' => $category->id]);
            }
        }

        $packages = [
            [
                'name' => 'Royal Wedding Jaipur Package',
                'slug' => 'royal-wedding-jaipur-package',
                'summary' => 'Palace-inspired décor, hospitality and coordination for Jaipur celebrations.',
                'price_label' => 'Starting ₹4,99,999',
                'price_amount' => 499999,
                'features' => ['Stage & floral décor', 'Guest hospitality desk', 'Photography coverage', 'DJ Night coordination'],
                'category' => 'wedding-celebrations',
                'service' => 'wedding-planning',
            ],
            [
                'name' => 'Destination Wedding Udaipur Package',
                'slug' => 'destination-wedding-udaipur-package',
                'summary' => 'Lake-city destination planning with venue and vendor management.',
                'price_label' => 'Starting ₹6,49,999',
                'price_amount' => 649999,
                'features' => ['Venue shortlisting', 'Travel desk support', 'Mehndi & Haldi styling', 'Reception décor'],
                'category' => 'wedding-celebrations',
                'service' => 'wedding-planning',
            ],
            [
                'name' => 'Luxury Catering Experience',
                'slug' => 'luxury-catering-experience',
                'summary' => 'Rajasthani and multi-cuisine menus crafted for grand gatherings.',
                'price_label' => 'Starting ₹1,249 / plate',
                'price_amount' => 1249,
                'features' => ['Live counters', 'Sweet & savory stations', 'Service staff', 'Menu tasting'],
                'category' => 'hospitality-media',
                'service' => 'catering',
            ],
            [
                'name' => 'DJ Night Entertainment',
                'slug' => 'dj-night-entertainment',
                'summary' => 'Professional DJ, sound and lighting for sangeet and after-parties.',
                'price_label' => 'Starting ₹45,000',
                'price_amount' => 45000,
                'features' => ['DJ console', 'LED lighting', 'Sound engineers', 'Playlist curation'],
                'category' => 'hospitality-media',
                'service' => 'dj-sound',
            ],
        ];

        foreach ($packages as $index => $package) {
            ServicePackage::query()->updateOrCreate(
                ['slug' => $package['slug']],
                [
                    'name' => $package['name'],
                    'summary' => $package['summary'],
                    'description' => '<p>'.$package['summary'].' Crafted by Balaji Royal Events for premium celebrations across Rajasthan.</p>',
                    'price_label' => $package['price_label'],
                    'price_amount' => $package['price_amount'],
                    'currency' => 'INR',
                    'features' => $package['features'],
                    'is_featured' => $index < 2,
                    'sort_order' => $index + 1,
                    'status' => true,
                    'is_visible' => true,
                    'service_category_id' => ServiceCategory::query()->where('slug', $package['category'])->value('id'),
                    'service_id' => Service::query()->where('slug', $package['service'])->value('id'),
                    'seo_title' => $package['name'].' | Balaji Royal Events',
                    'seo_description' => $package['summary'],
                ]
            );
        }
    }

    private function seedStatistics(): void
    {
        $stats = [
            ['label' => 'Weddings Planned', 'value' => '850', 'suffix' => '+', 'icon' => 'heart', 'sort_order' => 1],
            ['label' => 'Destination Events', 'value' => '220', 'suffix' => '+', 'icon' => 'map', 'sort_order' => 2],
            ['label' => 'Happy Families', 'value' => '1200', 'suffix' => '+', 'icon' => 'users', 'sort_order' => 3],
            ['label' => 'Years of Experience', 'value' => '12', 'suffix' => '+', 'icon' => 'calendar', 'sort_order' => 4],
        ];

        foreach ($stats as $stat) {
            Statistic::query()->updateOrCreate(
                ['label' => $stat['label']],
                [...$stat, 'status' => true, 'is_visible' => true, 'show_on_homepage' => true]
            );
        }
    }

    private function seedTeam(): void
    {
        $members = [
            ['name' => 'Rajesh Sharma', 'role' => 'Founder & Wedding Director', 'bio' => 'Leads royal and destination wedding planning across Jaipur, Udaipur and Shekhawati.', 'sort_order' => 1],
            ['name' => 'Pooja Agarwal', 'role' => 'Creative Décor Head', 'bio' => 'Designs stage decorations, floral installations and reception themes.', 'sort_order' => 2],
            ['name' => 'Amit Singh', 'role' => 'Hospitality Manager', 'bio' => 'Coordinates luxury catering, guest flow and venue hospitality desks.', 'sort_order' => 3],
            ['name' => 'Neha Verma', 'role' => 'Photography Lead', 'bio' => 'Captures Mehndi, Haldi, pheras and reception stories with cinematic coverage.', 'sort_order' => 4],
        ];

        foreach ($members as $member) {
            TeamMember::query()->updateOrCreate(
                ['name' => $member['name']],
                [...$member, 'status' => true, 'is_visible' => true, 'email' => 'balajievents19@gmail.com']
            );
        }
    }

    private function seedTestimonials(): void
    {
        $reviews = [
            ['name' => 'Ananya & Rohan', 'quote' => 'Our Destination Wedding Udaipur felt effortless. Balaji Royal Events handled venues, décor and hospitality with true Rajasthani warmth.', 'rating' => 5],
            ['name' => 'Kavita Sharma', 'quote' => 'From Mehndi Ceremony to Reception, every detail was elegant. The stage decorations and flower décor were breathtaking.', 'rating' => 5],
            ['name' => 'Vikram Jain', 'quote' => 'Professional team for our Corporate Event in Jaipur. Punctual, creative and transparent on pricing.', 'rating' => 5],
            ['name' => 'Sneha Rathore', 'quote' => 'Google Reviews cannot capture how smooth our Anniversary Event was. Photography and DJ Night were outstanding.', 'rating' => 4],
        ];

        foreach ($reviews as $index => $review) {
            Testimonial::query()->updateOrCreate(
                ['name' => $review['name'], 'type' => TestimonialType::ClientSays],
                [
                    'quote' => $review['quote'],
                    'rating' => $review['rating'],
                    'sort_order' => $index + 1,
                    'featured' => true,
                    'homepage_featured' => true,
                    'status' => true,
                ]
            );
        }

        $stories = [
            [
                'name' => 'Royal Wedding Jaipur',
                'body' => 'A three-day royal celebration with palace décor, live catering counters and a sparkling DJ Night. The family called it their most memorable Shekhawati-to-Jaipur journey.',
                'video_url' => null,
            ],
            [
                'name' => 'Haldi & Mehndi Weekend',
                'body' => 'Bright florals, traditional music and intimate guest hospitality made this Haldi Ceremony and Mehndi Ceremony weekend glow from morning to midnight.',
            ],
        ];

        foreach ($stories as $index => $story) {
            Testimonial::query()->updateOrCreate(
                ['name' => $story['name'], 'type' => TestimonialType::SuccessStory],
                [
                    'body' => $story['body'],
                    'video_url' => $story['video_url'] ?? null,
                    'rating' => 5,
                    'sort_order' => $index + 1,
                    'featured' => true,
                    'homepage_featured' => true,
                    'status' => true,
                ]
            );
        }
    }

    private function seedEventOverviews(): void
    {
        $fallbackImage = Service::query()->value('featured_image')
            ?? GalleryItem::query()->value('image')
            ?? 'event-overviews/placeholder.jpg';

        $events = [
            ['title' => 'Wedding Planning', 'caption' => 'Complete coordination', 'description' => 'From rituals to reception hospitality.', 'link_url' => '/services/wedding-planning', 'sort_order' => 1],
            ['title' => 'Destination Wedding Udaipur', 'caption' => 'Lakeside grandeur', 'description' => 'Venue, décor and guest experiences.', 'link_url' => '/services', 'sort_order' => 2],
            ['title' => 'Birthday Celebration', 'caption' => 'Joyful milestones', 'description' => 'Themes, cake moments and entertainment.', 'link_url' => '/services/event-management', 'sort_order' => 3],
            ['title' => 'Corporate Events', 'caption' => 'Business gatherings', 'description' => 'Conferences, launches and gala dinners.', 'link_url' => '/services/event-management', 'sort_order' => 4],
        ];

        foreach ($events as $event) {
            EventOverview::query()->updateOrCreate(
                ['title' => $event['title']],
                [
                    ...$event,
                    'image' => $fallbackImage,
                    'featured' => true,
                    'homepage_featured' => true,
                    'status' => true,
                ]
            );
        }
    }

    private function seedCtas(): void
    {
        $ctas = [
            [
                'key' => CtaSectionKey::HomeMid,
                'title' => 'Plan Your Royal Wedding with Balaji Royal Events',
                'subtitle' => 'Trusted across Jhunjhunu, Jaipur and Udaipur',
                'body' => 'Share your date and celebration style. Our planners will craft a clear roadmap for décor, catering, photography and hospitality.',
                'button_text' => 'Talk to Planner',
                'button_url' => '/contact',
                'secondary_button_text' => 'View Services',
                'secondary_button_url' => '/services',
                'show_on_homepage' => true,
                'sort_order' => 1,
            ],
            [
                'key' => CtaSectionKey::HomeBottom,
                'title' => 'Ready for a Destination Celebration?',
                'subtitle' => 'Udaipur • Jaipur • Shekhawati',
                'body' => 'Book a complimentary consultation for Destination Wedding Udaipur or Royal Wedding Jaipur packages.',
                'button_text' => 'Book Consultation',
                'button_url' => '/contact',
                'show_on_homepage' => true,
                'sort_order' => 2,
            ],
            [
                'key' => CtaSectionKey::ContactBanner,
                'title' => 'Visit Our Jhunjhunu Office',
                'subtitle' => 'Walk-ins welcome by appointment',
                'button_text' => 'Get Directions',
                'button_url' => '/contact',
                'show_on_homepage' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($ctas as $cta) {
            CtaSection::query()->updateOrCreate(
                ['key' => $cta['key']],
                [...$cta, 'status' => true, 'is_visible' => true]
            );
        }
    }

    private function seedOffices(): void
    {
        OfficeLocation::query()->updateOrCreate(
            ['name' => 'Jhunjhunu Head Office'],
            [
                'address' => 'Shop No.15, Road No.3, Opposite Jamuna Resort',
                'city' => 'Jhunjhunu',
                'state' => 'Rajasthan',
                'pincode' => '333001',
                'phone' => '+91-9462577065',
                'email' => 'balajievents19@gmail.com',
                'map_embed' => Setting::singleton()->google_map_embed,
                'is_primary' => true,
                'sort_order' => 1,
                'status' => true,
                'is_visible' => true,
            ]
        );

        OfficeLocation::query()->updateOrCreate(
            ['name' => 'Jaipur Coordination Desk'],
            [
                'address' => 'By appointment for Royal Wedding Jaipur planning meetings',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'phone' => '+91-9462577065',
                'email' => 'balajievents19@gmail.com',
                'is_primary' => false,
                'sort_order' => 2,
                'status' => true,
                'is_visible' => true,
            ]
        );
    }

    private function seedRedirects(): void
    {
        Redirect::query()->updateOrCreate(
            ['from_path' => '/old-home'],
            [
                'to_url' => '/',
                'status_code' => RedirectStatusCode::Permanent,
                'status' => true,
                'is_visible' => true,
                'notes' => 'Legacy homepage path',
                'sort_order' => 1,
            ]
        );
    }
}
